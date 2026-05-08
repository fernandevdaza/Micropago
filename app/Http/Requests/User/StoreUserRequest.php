<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'ci'                => 'required|string|unique:users,ci',
            'date_of_birth'     => 'required|date|before:today',
            'role'              => 'required|in:passenger,driver,admin,super_admin,line_admin',
            'transport_line_id' => 'nullable|exists:transport_lines,id',
        ];
    }
}
