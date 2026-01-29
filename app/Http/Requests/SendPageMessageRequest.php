<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPageMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'message' => ['required', 'string', 'min:3', 'max:10000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => __('Email'),
            'message' => __('Message'),
        ];
    }
}
