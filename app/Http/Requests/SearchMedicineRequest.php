<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_ids' => 'required|array|min:1',
            'medicine_ids.*' => 'exists:medicines,id',
            'latitude' => 'required|numeric|between:32.0,37.5',
            'longitude' => 'required|numeric|between:35.5,42.5',
            'radius' => 'nullable|numeric|min:1|max:50',
        ];
    }
}