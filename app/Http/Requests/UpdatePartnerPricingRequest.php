<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerPricingRequest extends FormRequest
{
    /**
     * La ruta ya está protegida por el middleware permission:partner-pricing.manage.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalizar inputs vacíos del widget/API a NULL para que hereden del catálogo.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'api_markup_percentage' => $this->filled('api_markup_percentage') ? $this->input('api_markup_percentage') : null,
            'api_tier_id' => $this->filled('api_tier_id') ? $this->input('api_tier_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'markup_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'current_tier_id' => ['nullable', 'exists:pricing_tiers,id'],
            'manual_tier_override' => ['boolean'],
            // Pricing independiente del widget/API. NULL = heredar del catálogo.
            'api_markup_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'api_tier_id' => ['nullable', 'exists:pricing_tiers,id'],
        ];
    }
}
