<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use App\Support\Validacion\ReglasValidacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [
                ...ReglasValidacion::email(nullable: false),
                Rule::unique(Usuario::class)->ignore($this->user()->id),
            ],
        ];
    }
}
