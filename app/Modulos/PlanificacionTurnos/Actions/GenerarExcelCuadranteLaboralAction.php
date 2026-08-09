<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Models\Usuario;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Genera la representación Excel de un cuadrante publicado y la guarda privada.
 *
 * La base de datos sigue siendo la fuente de verdad. El archivo es una captura
 * inmutable para distribuir, imprimir o archivar cada versión publicada.
 */
final class GenerarExcelCuadranteLaboralAction
{
    private const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const MINUTOS_INTERVALO = 30;

    private const COLOR_CABECERA = '0F172A';

    private const COLOR_DIA = 'E2E8F0';

    private const COLOR_REJILLA = 'CBD5E1';

    private const COLOR_TURNO = '22C55E';

    /** @var array<string, string> */
    private const COLORES_INCIDENCIA = [
        TipoIncidenciaLaboral::Descanso->value => 'FACC15',
        TipoIncidenciaLaboral::Vacaciones->value => '0EA5E9',
        TipoIncidenciaLaboral::Baja->value => 'EF4444',
        TipoIncidenciaLaboral::Ausencia->value => 'F97316',
        TipoIncidenciaLaboral::Festivo->value => '8B5CF6',
    ];

    /** @var list<string> */
    private const NOMBRES_DIA = [
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo',
    ];

    /**
     * @return array{
     *     disk: string,
     *     ruta: string,
     *     nombre_archivo: string,
     *     mime_type: string,
     *     tamano_bytes: int,
     *     hash_sha256: string
     * }
     */
    public function ejecutar(
        CuadranteLaboral $cuadrante,
        int $version,
        Usuario $generadoPor,
        Carbon $generadoAt,
    ): array {
        $cuadrante->loadMissing([
            'jornadas.usuario',
            'jornadas.areaTrabajo',
        ]);

        $incidencias = IncidenciaLaboral::query()
            ->with('usuario')
            ->coincideConPeriodo($cuadrante->semana_inicio, $cuadrante->semanaFin())
            ->orderBy('fecha_inicio')
            ->get();

        $spreadsheet = $this->crearLibro(
            $cuadrante,
            $incidencias,
            $version,
            $generadoPor,
            $generadoAt,
        );

        $disk = (string) config('planificacion-turnos.exportaciones.disk', 'local');
        $directorio = trim((string) config(
            'planificacion-turnos.exportaciones.directorio',
            'planificacion-turnos/cuadrantes',
        ), '/');
        $nombreArchivo = sprintf(
            'cuadrante-%s-v%03d.xlsx',
            $cuadrante->semana_inicio->format('Y-m-d'),
            $version,
        );
        $ruta = $directorio.'/'.$cuadrante->getKey().'/'.$nombreArchivo;
        $temporal = tempnam(sys_get_temp_dir(), 'cuadrante_');

        if ($temporal === false) {
            throw new RuntimeException('No se ha podido crear el archivo temporal del cuadrante.');
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($temporal);

            $tamano = filesize($temporal);
            $hash = hash_file('sha256', $temporal);
            $stream = fopen($temporal, 'rb');

            if ($tamano === false || $hash === false || $stream === false) {
                throw new RuntimeException('No se han podido calcular los metadatos del Excel generado.');
            }

            try {
                $guardado = Storage::disk($disk)->put($ruta, $stream);
            } finally {
                fclose($stream);
            }

            if (! $guardado) {
                throw new RuntimeException('No se ha podido guardar el Excel del cuadrante.');
            }

            return [
                'disk' => $disk,
                'ruta' => $ruta,
                'nombre_archivo' => $nombreArchivo,
                'mime_type' => self::MIME_XLSX,
                'tamano_bytes' => $tamano,
                'hash_sha256' => $hash,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();

            if (is_file($temporal)) {
                unlink($temporal);
            }
        }
    }

    /**
     * @param  Collection<int, IncidenciaLaboral>  $incidencias
     */
    private function crearLibro(
        CuadranteLaboral $cuadrante,
        Collection $incidencias,
        int $version,
        Usuario $generadoPor,
        Carbon $generadoAt,
    ): Spreadsheet {
        [$inicioRejilla, $finRejilla] = $this->limitesRejilla($cuadrante->jornadas);
        $numeroIntervalos = (int) (($finRejilla - $inicioRejilla) / self::MINUTOS_INTERVALO);
        $ultimaColumna = Coordinate::stringFromColumnIndex(2 + $numeroIntervalos);
        $negocio = ConfiguracionNegocio::actual();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator($negocio->nombre_comercial)
            ->setLastModifiedBy($generadoPor->nombre)
            ->setTitle('Cuadrante semanal '.$cuadrante->semana_inicio->format('d/m/Y'))
            ->setSubject('Planificación de turnos publicada')
            ->setDescription('Versión '.$version.' del cuadrante semanal publicado.');

        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Cuadrante');
        $hoja->setShowGridlines(false);
        $hoja->freezePane('C6');

        $this->crearCabecera(
            $hoja,
            $cuadrante,
            $version,
            $generadoPor,
            $generadoAt,
            $negocio->nombre_comercial,
            $ultimaColumna,
        );

        $grupos = $this->gruposPorArea($cuadrante, $incidencias);
        $fila = 6;

        foreach (range(0, 6) as $desplazamiento) {
            $fecha = $cuadrante->semana_inicio->copy()->addDays($desplazamiento);
            $festivos = $incidencias
                ->filter(fn (IncidenciaLaboral $incidencia): bool => $incidencia->esGlobal() && $incidencia->afectaFecha($fecha));

            $this->crearCabeceraDia($hoja, $fila, $fecha, $festivos, $ultimaColumna);
            $fila++;

            foreach ($grupos as $grupo) {
                $this->crearCabeceraArea(
                    $hoja,
                    $fila,
                    $grupo['nombre'],
                    $grupo['color'],
                    $inicioRejilla,
                    $numeroIntervalos,
                );
                $fila++;

                foreach ($grupo['usuarios'] as $usuario) {
                    $jornadas = $cuadrante->jornadas
                        ->filter(fn (JornadaLaboral $jornada): bool => $jornada->usuario_id === $usuario->id
                            && $jornada->fecha->isSameDay($fecha)
                            && ($jornada->area_trabajo_id ?? 'sin-area') === $grupo['clave'])
                        ->sortBy('hora_inicio')
                        ->values();
                    $incidencia = $incidencias->first(
                        fn (IncidenciaLaboral $item): bool => ! $item->esGlobal()
                            && $item->usuario_id === $usuario->id
                            && $item->afectaFecha($fecha),
                    );

                    $this->crearFilaEmpleado(
                        $hoja,
                        $fila,
                        $usuario,
                        $jornadas,
                        $incidencia,
                        $inicioRejilla,
                        $numeroIntervalos,
                    );
                    $fila++;
                }

                $fila++;
            }
        }

        $ultimaFila = max(1, $fila - 1);
        $this->configurarHoja($hoja, $ultimaColumna, $ultimaFila, $numeroIntervalos);

        return $spreadsheet;
    }

    private function crearCabecera(
        Worksheet $hoja,
        CuadranteLaboral $cuadrante,
        int $version,
        Usuario $generadoPor,
        Carbon $generadoAt,
        string $nombreNegocio,
        string $ultimaColumna,
    ): void {
        $hoja->mergeCells("A1:{$ultimaColumna}1");
        $hoja->setCellValue('A1', mb_strtoupper($nombreNegocio).' · CUADRANTE DE TURNOS');
        $hoja->getStyle("A1:{$ultimaColumna}1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_CABECERA]],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $hoja->getRowDimension(1)->setRowHeight(30);

        $hoja->mergeCells("A2:{$ultimaColumna}2");
        $hoja->setCellValue('A2', sprintf(
            'DEL %s AL %s · VERSIÓN %03d',
            $cuadrante->semana_inicio->format('d/m/Y'),
            $cuadrante->semanaFin()->format('d/m/Y'),
            $version,
        ));
        $hoja->getStyle("A2:{$ultimaColumna}2")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_CABECERA], 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $hoja->mergeCells("A3:{$ultimaColumna}3");
        $hoja->setCellValue('A3', sprintf(
            'Publicado el %s por %s · Documento generado automáticamente desde el panel',
            $generadoAt->format('d/m/Y H:i'),
            $generadoPor->nombre,
        ));
        $hoja->getStyle("A3:{$ultimaColumna}3")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '475569'], 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $leyenda = [
            ['B4', 'Turno', 'C4', self::COLOR_TURNO],
            ['D4', 'Descanso', 'E4', self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Descanso->value]],
            ['F4', 'Vacaciones', 'G4', self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Vacaciones->value]],
            ['H4', 'Baja', 'I4', self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Baja->value]],
            ['J4', 'Ausencia', 'K4', self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Ausencia->value]],
            ['L4', 'Festivo', 'M4', self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Festivo->value]],
        ];

        $hoja->setCellValue('A4', 'LEYENDA');
        $hoja->getStyle('A4')->getFont()->setBold(true);

        foreach ($leyenda as [$celdaTexto, $texto, $celdaColor, $color]) {
            $hoja->setCellValue($celdaTexto, $texto);
            $hoja->getStyle($celdaTexto)->getFont()->setSize(9);
            $hoja->getStyle($celdaColor)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_REJILLA]]],
            ]);
        }
    }

    /**
     * @param  Collection<int, IncidenciaLaboral>  $festivos
     */
    private function crearCabeceraDia(
        Worksheet $hoja,
        int $fila,
        Carbon $fecha,
        Collection $festivos,
        string $ultimaColumna,
    ): void {
        $hoja->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
        $textoFestivo = $festivos->isEmpty()
            ? ''
            : ' · FESTIVO: '.$festivos->map(fn (IncidenciaLaboral $festivo): string => $festivo->notas ?: 'Sin detalle')->implode(' / ');
        $hoja->setCellValue(
            "A{$fila}",
            mb_strtoupper(self::NOMBRES_DIA[$fecha->dayOfWeekIso - 1]).' · '.$fecha->format('d/m/Y').$textoFestivo,
        );
        $color = $festivos->isEmpty() ? self::COLOR_DIA : self::COLORES_INCIDENCIA[TipoIncidenciaLaboral::Festivo->value];
        $colorTexto = $festivos->isEmpty() ? self::COLOR_CABECERA : 'FFFFFF';
        $hoja->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            'font' => ['bold' => true, 'color' => ['rgb' => $colorTexto], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::COLOR_CABECERA]]],
        ]);
        $hoja->getRowDimension($fila)->setRowHeight(24);
    }

    private function crearCabeceraArea(
        Worksheet $hoja,
        int $fila,
        string $nombreArea,
        string $colorArea,
        int $inicioRejilla,
        int $numeroIntervalos,
    ): void {
        $ultimaColumna = Coordinate::stringFromColumnIndex(2 + $numeroIntervalos);
        $hoja->setCellValue("A{$fila}", mb_strtoupper($nombreArea));
        $hoja->setCellValue("B{$fila}", 'HORAS');

        foreach (range(0, $numeroIntervalos - 1) as $intervalo) {
            $columna = Coordinate::stringFromColumnIndex(3 + $intervalo);
            $hoja->setCellValue("{$columna}{$fila}", $this->etiquetaHora($inicioRejilla + ($intervalo * self::MINUTOS_INTERVALO)));
        }

        $hoja->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_CABECERA], 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_REJILLA]]],
        ]);
        $hoja->getStyle("A{$fila}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ltrim($colorArea, '#')]],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $hoja->getRowDimension($fila)->setRowHeight(22);
    }

    /**
     * @param  Collection<int, JornadaLaboral>  $jornadas
     */
    private function crearFilaEmpleado(
        Worksheet $hoja,
        int $fila,
        Usuario $usuario,
        Collection $jornadas,
        ?IncidenciaLaboral $incidencia,
        int $inicioRejilla,
        int $numeroIntervalos,
    ): void {
        $ultimaColumna = Coordinate::stringFromColumnIndex(2 + $numeroIntervalos);
        $detalleHorario = $jornadas
            ->map(fn (JornadaLaboral $jornada): string => $this->etiquetaJornada($jornada))
            ->implode(' / ');
        $hoja->setCellValue("A{$fila}", $usuario->nombre.($detalleHorario !== '' ? "\n".$detalleHorario : ''));
        $hoja->setCellValue("B{$fila}", round($jornadas->sum(
            fn (JornadaLaboral $jornada): int => $jornada->minutosEfectivos(),
        ) / 60, 2));
        $hoja->getStyle("B{$fila}")->getNumberFormat()->setFormatCode('0.00 "h"');
        $hoja->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_REJILLA]]],
        ]);
        $hoja->getStyle("A{$fila}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $hoja->getStyle("B{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getRowDimension($fila)->setRowHeight(34);

        if ($incidencia !== null) {
            $primeraColumna = Coordinate::stringFromColumnIndex(3);
            $hoja->mergeCells("{$primeraColumna}{$fila}:{$ultimaColumna}{$fila}");
            $hoja->setCellValue("{$primeraColumna}{$fila}", mb_strtoupper($incidencia->tipo->etiqueta()));
            $hoja->getStyle("{$primeraColumna}{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLORES_INCIDENCIA[$incidencia->tipo->value]],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_REJILLA]]],
            ]);

            return;
        }

        foreach ($jornadas as $jornada) {
            [$inicio, $fin] = $this->minutosJornada($jornada);
            $indiceInicio = max(0, (int) floor(($inicio - $inicioRejilla) / self::MINUTOS_INTERVALO));
            $indiceFin = min(
                $numeroIntervalos - 1,
                max($indiceInicio, (int) ceil(($fin - $inicioRejilla) / self::MINUTOS_INTERVALO) - 1),
            );
            $columnaInicio = Coordinate::stringFromColumnIndex(3 + $indiceInicio);
            $columnaFin = Coordinate::stringFromColumnIndex(3 + $indiceFin);
            $hoja->getStyle("{$columnaInicio}{$fila}:{$columnaFin}{$fila}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_TURNO]],
            ]);
        }
    }

    private function configurarHoja(
        Worksheet $hoja,
        string $ultimaColumna,
        int $ultimaFila,
        int $numeroIntervalos,
    ): void {
        $hoja->getColumnDimension('A')->setWidth(28);
        $hoja->getColumnDimension('B')->setWidth(11);

        foreach (range(0, $numeroIntervalos - 1) as $intervalo) {
            $hoja->getColumnDimension(Coordinate::stringFromColumnIndex(3 + $intervalo))->setWidth(7.2);
        }

        $hoja->getStyle("A1:{$ultimaColumna}{$ultimaFila}")->getFont()->setName('Calibri');
        $hoja->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $hoja->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25);
        $hoja->getPageSetup()->setPrintArea("A1:{$ultimaColumna}{$ultimaFila}");
        $hoja->getHeaderFooter()->setOddFooter('&L'.$hoja->getTitle().'&RPágina &P de &N');
    }

    /**
     * Agrupa el personal por las áreas realmente utilizadas durante la semana.
     * Las personas con incidencia pero sin ningún turno aparecen en un bloque
     * explícito para que vacaciones o bajas no desaparezcan del documento.
     *
     * @param  Collection<int, IncidenciaLaboral>  $incidencias
     * @return Collection<int, array{clave: string, nombre: string, color: string, orden: int, usuarios: Collection<int, Usuario>}>
     */
    private function gruposPorArea(CuadranteLaboral $cuadrante, Collection $incidencias): Collection
    {
        $grupos = $cuadrante->jornadas
            ->groupBy(fn (JornadaLaboral $jornada): string => $jornada->area_trabajo_id ?? 'sin-area')
            ->map(function (Collection $jornadas, string $clave): array {
                $area = $jornadas->first()?->areaTrabajo;

                return [
                    'clave' => $clave,
                    'nombre' => $area?->nombre ?? 'Sin área asignada',
                    'color' => $area?->color ?? '#64748B',
                    'orden' => $area?->orden ?? 999,
                    'usuarios' => $jornadas
                        ->pluck('usuario')
                        ->filter()
                        ->unique('id')
                        ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                        ->values(),
                ];
            });

        $usuariosProgramados = $cuadrante->jornadas->pluck('usuario_id')->unique();
        $usuariosSinArea = $incidencias
            ->reject(fn (IncidenciaLaboral $incidencia): bool => $incidencia->esGlobal() || $incidencia->usuario === null)
            ->reject(fn (IncidenciaLaboral $incidencia): bool => $usuariosProgramados->contains($incidencia->usuario_id))
            ->pluck('usuario')
            ->unique('id')
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($usuariosSinArea->isNotEmpty()) {
            $sinArea = $grupos->get('sin-area', [
                'clave' => 'sin-area',
                'nombre' => 'Sin área asignada',
                'color' => '#64748B',
                'orden' => 999,
                'usuarios' => collect(),
            ]);
            $sinArea['usuarios'] = $sinArea['usuarios']
                ->concat($usuariosSinArea)
                ->unique('id')
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
            $grupos->put('sin-area', $sinArea);
        }

        return $grupos
            ->sortBy(fn (array $grupo): string => str_pad((string) $grupo['orden'], 4, '0', STR_PAD_LEFT).$grupo['nombre'])
            ->values();
    }

    /**
     * @param  Collection<int, JornadaLaboral>  $jornadas
     * @return array{int, int}
     */
    private function limitesRejilla(Collection $jornadas): array
    {
        $inicios = $jornadas->map(fn (JornadaLaboral $jornada): int => $this->minutosJornada($jornada)[0]);
        $finales = $jornadas->map(fn (JornadaLaboral $jornada): int => $this->minutosJornada($jornada)[1]);
        $inicio = min(8 * 60, $inicios->min() ?? 8 * 60);
        $fin = max(22 * 60, $finales->max() ?? 22 * 60);

        return [
            (int) floor($inicio / self::MINUTOS_INTERVALO) * self::MINUTOS_INTERVALO,
            (int) ceil($fin / self::MINUTOS_INTERVALO) * self::MINUTOS_INTERVALO,
        ];
    }

    /** @return array{int, int} */
    private function minutosJornada(JornadaLaboral $jornada): array
    {
        $inicio = $this->horaAMinutos($jornada->hora_inicio);
        $fin = $this->horaAMinutos($jornada->hora_fin) + ($jornada->termina_dia_siguiente ? 24 * 60 : 0);

        return [$inicio, $fin];
    }

    private function horaAMinutos(string $hora): int
    {
        [$horas, $minutos] = array_map('intval', explode(':', $hora));

        return ($horas * 60) + $minutos;
    }

    private function etiquetaJornada(JornadaLaboral $jornada): string
    {
        return substr($jornada->hora_inicio, 0, 5)
            .'–'.substr($jornada->hora_fin, 0, 5)
            .($jornada->termina_dia_siguiente ? ' +1' : '');
    }

    private function etiquetaHora(int $minutos): string
    {
        $diaSiguiente = $minutos >= 24 * 60;
        $minutos %= 24 * 60;

        return sprintf('%02d:%02d%s', intdiv($minutos, 60), $minutos % 60, $diaSiguiente ? '+1' : '');
    }
}
