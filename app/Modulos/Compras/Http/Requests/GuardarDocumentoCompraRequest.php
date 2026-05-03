<?php

namespace App\Modulos\Compras\Http\Requests;

use App\Modulos\Compras\Enums\TipoDocumentoCompra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarDocumentoCompraRequest extends FormRequest
{
    /**
     * Autoriza subida de documentos a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion del documento.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['nullable', 'uuid', 'exists:proveedores,id'],
            'tipo_documento' => ['required', 'string', Rule::in(TipoDocumentoCompra::valores())],
            'archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'notas' => ['nullable', 'string', 'max:1000'],
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
            'tipo_documento.required' => 'El campo tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es valido.',
            'archivo.required' => 'El campo archivo es obligatorio.',
            'archivo.file' => 'El campo archivo debe ser un fichero valido.',
            'archivo.mimes' => 'El archivo debe ser una imagen JPG, PNG o un PDF.',
            'archivo.max' => 'El archivo no puede superar 10 MB.',
            'notas.max' => 'El campo notas no puede superar 1000 caracteres.',
        ];
    }

    /**
     * Datos normalizados para el documento.
     *
     * @return array<string, mixed>
     */
    public function datosDocumento(): array
    {
        return [
            'proveedor_id' => $this->input('proveedor_id') ?: null,
            'tipo_documento' => $this->input('tipo_documento'),
            'notas' => trim((string) $this->input('notas', '')) ?: null,
        ];
    }
}
