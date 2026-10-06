<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminLoginCustomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Custom validation rules for administrator sign-in.
     * Enforces non-generic, domain-specific security checks.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Non-generic, domain-specific error feedback messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email and password need must',
            'email.email' => 'Incorrect email or password',
            'email.max' => 'Incorrect email or password',
            'password.required' => 'Email and password need must',
        ];
    }

    /**
     * Configure the custom validator instance for domain-specific checks.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // If primary syntax validation failed, do not proceed to deep domain checks
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // Custom domain check: Account suspension verification
            $admin = Admin::where('email', strtolower(trim((string) $this->input('email'))))->first();
            if ($admin && strtolower($admin->status) === 'suspended') {
                $validator->errors()->add(
                    'email',
                    'Access Denied: This administrator account has been suspended by Security Compliance. Contact system administrators.'
                );
            }
        });
    }
}
