<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RechargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPlatformOperator();
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:1',
        ];
    }
}
