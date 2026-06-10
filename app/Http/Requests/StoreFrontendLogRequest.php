<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFrontendLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:js-error,unhandled-rejection,resource-error,loader-stuck',
            'message' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:500',
            'line' => 'nullable|integer',
            'column' => 'nullable|integer',
            'stack' => 'nullable|string|max:4000',
            'url' => 'nullable|string|max:500',
            'diagnostics' => 'nullable|array',
        ];
    }
}
