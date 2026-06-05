<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
        ];
    }
}