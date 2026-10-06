<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $admin = $this->route('admin');
        $adminId = is_object($admin) ? $admin->id : ($admin ?? $this->input('id'));
        $isCreate = $this->isMethod('post');

        $validRoles = [
            'Super Admin',
            'Product Manager',
            'Health Content Reviewer',
            'Submission Reviewer',
            'Marketing Manager',
            'Support Viewer',
        ];

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'password' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'min:6',
                'max:100',
            ],
            'role' => [
                'required',
                'string',
                Rule::in($validRoles),
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['Active', 'Suspended']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Staff full name is required.',
            'name.min' => 'Name must contain at least 2 characters.',
            'email.required' => 'A valid corporate email address is required.',
            'email.email' => 'Please provide a valid email format.',
            'email.unique' => 'An administrator or manager with this email already exists.',
            'password.required' => 'A secure initial password (at least 6 characters) is required.',
            'password.min' => 'Password must contain at least 6 characters.',
            'role.required' => 'Please designate an administrative role.',
            'role.in' => 'The selected role is not recognized by the security policy.',
            'status.required' => 'Please specify the account status.',
        ];
    }
}
