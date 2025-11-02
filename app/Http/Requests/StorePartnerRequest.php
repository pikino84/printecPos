<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Partner;
use Illuminate\Validation\Rule;

class StorePartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajusta según tu lógica de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:partners,slug',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'type' => ['required', Rule::in(Partner::TYPES)], // ✅ Validación dinámica
            'commercial_terms' => 'nullable|string',
            'comments' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de partner es obligatorio.',
            'type.in' => 'El tipo de partner seleccionado no es válido. Tipos permitidos: ' . implode(', ', Partner::TYPES),
            'name.required' => 'El nombre del partner es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'contact_email.email' => 'El correo de contacto debe ser una dirección válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'slug' => 'slug',
            'contact_name' => 'nombre de contacto',
            'contact_phone' => 'teléfono de contacto',
            'contact_email' => 'correo de contacto',
            'direccion' => 'dirección',
            'type' => 'tipo de partner',
            'commercial_terms' => 'condiciones comerciales',
            'comments' => 'comentarios',
            'is_active' => 'activo',
        ];
    }
}