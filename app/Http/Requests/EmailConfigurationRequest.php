<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\EmailConfiguration;

class EmailConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|between:1,65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'is_active' => 'boolean',
            'bank_patterns' => 'nullable|array',
            'bank_patterns.*' => 'string|max:100'
        ];

        // Handle email uniqueness for create vs update
        if ($this->isMethod('POST')) {
            // Creating new configuration
            $rules['email'] .= '|unique:email_configurations,email';
            $rules['password'] = 'required|string|min:6';
        } else {
            // Updating existing configuration
            $emailConfiguration = $this->getEmailConfiguration();
            
            if ($emailConfiguration) {
                $rules['email'] .= '|' . Rule::unique('email_configurations', 'email')->ignore($emailConfiguration->id);
            } else {
                // Fallback - don't ignore any ID for uniqueness check
                $rules['email'] .= '|unique:email_configurations,email';
            }
            $rules['password'] = 'nullable|string|min:6';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Configuration name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already configured.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'imap_host.required' => 'IMAP host is required.',
            'imap_port.required' => 'IMAP port is required.',
            'imap_port.between' => 'IMAP port must be between 1 and 65535.',
            'imap_encryption.required' => 'Encryption type is required.',
            'imap_encryption.in' => 'Encryption must be SSL, TLS, or None.',
            'bank_patterns.array' => 'Bank patterns must be an array.',
            'bank_patterns.*.string' => 'Each bank pattern must be a string.',
            'bank_patterns.*.max' => 'Each bank pattern must not exceed 100 characters.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up bank patterns - remove empty values
        if ($this->has('bank_patterns') && is_array($this->bank_patterns)) {
            $cleanPatterns = array_filter($this->bank_patterns, function ($pattern) {
                return is_string($pattern) && !empty(trim($pattern));
            });
            
            $this->merge([
                'bank_patterns' => array_values($cleanPatterns) // Re-index array
            ]);
        }

        // Convert is_active checkbox to boolean
        $this->merge([
            'is_active' => $this->boolean('is_active')
        ]);

        // Ensure numeric fields are properly cast
        if ($this->has('imap_port')) {
            $this->merge([
                'imap_port' => (int) $this->input('imap_port')
            ]);
        }
    }

    /**
     * Safely get the EmailConfiguration from route parameters
     */
    protected function getEmailConfiguration(): ?EmailConfiguration
    {
        try {
            // Try to get the bound model from the route
            $emailConfiguration = $this->route('emailConfiguration');
            
            // Check if it's already a model instance
            if ($emailConfiguration instanceof EmailConfiguration) {
                return $emailConfiguration;
            }
            
            // If it's an ID, try to find the model
            if (is_numeric($emailConfiguration)) {
                return EmailConfiguration::find($emailConfiguration);
            }
            
            // Try alternative route parameter names
            $emailConfiguration = $this->route('email_configuration');
            if ($emailConfiguration instanceof EmailConfiguration) {
                return $emailConfiguration;
            }
            
            if (is_numeric($emailConfiguration)) {
                return EmailConfiguration::find($emailConfiguration);
            }
            
            return null;
        } catch (\Exception $e) {
            // Log the error but don't fail validation
            \Log::warning('Failed to get EmailConfiguration from route: ' . $e->getMessage());
            return null;
        }
    }
}