<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Enums\EstadoDocumentoCompra;
use App\Modulos\Compras\Enums\TipoDocumentoCompra;
use App\Modulos\Compras\Http\Requests\GuardarDocumentoCompraRequest;
use App\Modulos\Compras\Models\DocumentoCompra;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentoCompraController extends Controller
{
    /**
     * Lista documentos subidos para lectura asistida.
     */
    public function index(): View
    {
        return view('modulos.compras.documentos.index', [
            'documentos' => DocumentoCompra::query()
                ->with(['proveedor', 'subidor', 'borrador'])
                ->latest()
                ->paginate(15),
        ]);
    }

    /**
     * Muestra formulario de subida de documento.
     */
    public function create(): View
    {
        return view('modulos.compras.documentos.create', [
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'tiposDocumento' => TipoDocumentoCompra::cases(),
        ]);
    }

    /**
     * Guarda el archivo y crea trazabilidad inicial de lectura/borrador.
     */
    public function store(GuardarDocumentoCompraRequest $request): RedirectResponse
    {
        $archivo = $request->file('archivo');

        $documento = DB::transaction(function () use ($request, $archivo): DocumentoCompra {
            $nombreArchivo = Str::uuid().'.'.$archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('documentos_compra', $nombreArchivo, 'local');

            $documento = DocumentoCompra::query()->create(array_merge($request->datosDocumento(), [
                'estado' => EstadoDocumentoCompra::Pendiente,
                'nombre_original' => $archivo->getClientOriginalName(),
                'disco' => 'local',
                'ruta_archivo' => $ruta,
                'mime_type' => $archivo->getMimeType(),
                'tamano_bytes' => $archivo->getSize() ?: 0,
                'subido_por' => $request->user()?->id,
            ]));

            $documento->lecturas()->create([
                'motor' => 'pendiente',
                'estado' => 'pendiente',
            ]);

            $documento->borrador()->create([
                'estado' => 'pendiente_revision',
                'datos_borrador' => [
                    'proveedor_id' => $documento->proveedor_id,
                    'lineas' => [],
                ],
            ]);

            return $documento;
        });

        return redirect()->route('admin.compras.documentos.show', $documento)
            ->with('status', 'Documento subido correctamente. Queda pendiente de lectura asistida y revision humana.');
    }

    /**
     * Muestra detalle de documento, lectura y borrador asociado.
     */
    public function show(DocumentoCompra $documento): View
    {
        return view('modulos.compras.documentos.show', [
            'documento' => $documento->load(['proveedor', 'subidor', 'lecturas.procesador', 'borrador.revisor', 'borrador.pedido']),
            'archivoExiste' => Storage::disk($documento->disco)->exists($documento->ruta_archivo),
        ]);
    }
}
