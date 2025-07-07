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
            'email' => 'nullable|email|max:255|required_without:phone',
            'phone' => 'nullable|string|max:20|required_without:email',
            'client_name' => 'required|string|max:255',
            'email_template' => 'nullable|string|max:50',
            'project_name' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'timeframe' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Either an email address or WhatsApp number is required.',
            'phone.required_without' => 'Either a WhatsApp number or email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.max' => 'WhatsApp number must not exceed 20 characters.',
            'email.max' => 'Email address must not exceed 255 characters.',
        ];
    }
}