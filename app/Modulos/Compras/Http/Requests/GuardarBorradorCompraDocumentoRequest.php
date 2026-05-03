<?php

namespace App\Modulos\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarBorradorCompraDocumentoRequest extends FormRequest
{
    /**
     * Autoriza la revision de borradores a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion del borrador revisable.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['nullable', 'uuid', 'exists:proveedores,id'],
            'fecha_documento' => ['nullable', 'date'],
            'numero_documento' => ['nullable', 'string', 'max:100'],
            'notas_revision' => ['nullable', 'string', 'max:1000'],
            'lineas' => ['nullable', 'array'],
            'lineas.*.producto_id' => ['nullable', 'uuid', 'exists:productos,id'],
            'lineas.*.descripcion' => ['nullable', 'string', 'max:191'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0.001'],
            'lineas.*.coste_unitario' => ['nullable', 'numeric', 'min:0'],
            'lineas.*.iva_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Mensajes de validacion en espanol.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'fecha_documento.date' => 'El campo fecha del documento debe ser una fecha valida.',
            'numero_documento.max' => 'El campo numero del documento no puede superar 100 caracteres.',
            'notas_revision.max' => 'El campo notas de revision no puede superar 1000 caracteres.',
            'lineas.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'lineas.*.cantidad.min' => 'La cantidad de cada linea debe ser mayor que cero.',
            'lineas.*.coste_unitario.min' => 'El coste unitario no puede ser negativo.',
            'lineas.*.iva_porcentaje.max' => 'El IVA no puede ser superior al 100%.',
        ];
    }

    /**
     * Validacion cruzada para lineas parcialmente rellenadas.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('lineas', []) as $indice => $linea) {
                    $tieneAlguno = filled($linea['producto_id'] ?? null)
                        || filled($linea['descripcion'] ?? null)
                        || filled($linea['cantidad'] ?? null)
                        || filled($linea['coste_unitario'] ?? null);

                    $estaCompleta = filled($linea['producto_id'] ?? null)
                        && (float) ($linea['cantidad'] ?? 0) > 0;

                    if ($tieneAlguno && ! $estaCompleta) {
                        $validator->errors()->add("lineas.{$indice}.producto_id", 'Cada linea usada debe tener producto y cantidad mayor que cero.');
                    }
                }
            },
        ];
    }

    /**
     * Datos estructurados del borrador.
     *
     * @return array<string, mixed>
     */
    public function datosBorrador(): array
    {
        return [
            'proveedor_id' => $this->input('proveedor_id') ?: null,
            'fecha_documento' => $this->input('fecha_documento') ?: null,
            'numero_documento' => trim((string) $this->input('numero_documento', '')) ?: null,
            'lineas' => $this->lineasLimpias(),
        ];
    }

    /**
     * Lineas completas listas para crear pedido.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lineasLimpias(): array
    {
        $lineas = [];

        foreach ((array) $this->input('lineas', []) as $linea) {
            $productoId = (string) ($linea['producto_id'] ?? '');
            $cantidad = (float) ($linea['cantidad'] ?? 0);

            if ($productoId === '' && $cantidad <= 0) {
                continue;
            }

            if ($productoId === '' || $cantidad <= 0) {
                continue;
            }

            $lineas[] = [
                'producto_id' => $productoId,
                'descripcion' => trim((string) ($linea['descripcion'] ?? '')),
                'cantidad' => round($cantidad, 3),
                'coste_unitario' => round((float) ($linea['coste_unitario'] ?? 0), 2),
                'iva_porcentaje' => round((float) ($linea['iva_porcentaje'] ?? 21), 2),
            ];
        }

        return $lineas;
    }

    public function notasRevision(): ?string
    {
        return trim((string) $this->input('notas_revision', '')) ?: null;
    }
}
