<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'              => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|required|email|unique:users,email,' . $userId,
            'password'          => 'nullable|string|min:8|confirmed',
            'ci'                => 'sometimes|required|string|unique:users,ci,' . $userId,
            'date_of_birth'     => 'sometimes|required|date|before:today',
            'role'              => 'sometimes|required|in:passenger,driver,admin,super_admin,line_admin',
            'transport_line_id' => 'sometimes|nullable|exists:transport_lines,id',
        ];
    }
}
