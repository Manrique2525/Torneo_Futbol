<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscripcionPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'comprobante' => 'required_if:metodo_pago,transferencia|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'referencia' => 'nullable|string|max:100',
            'notas' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'comprobante.required_if' => 'El comprobante es obligatorio para pagos por transferencia.',
            'comprobante.max' => 'El comprobante no debe superar los 5MB.',
            'comprobante.mimes' => 'El comprobante debe ser JPG, PNG o PDF.',
        ];
    }
}
