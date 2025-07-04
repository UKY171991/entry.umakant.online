<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'client_name' => 'required|string|max:255',
            'email_template' => 'nullable|string|max:50',
            'project_name' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'timeframe' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}