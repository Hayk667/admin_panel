<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class StoreCategoryRequest extends FormRequest
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
        $languages = Language::where('is_active', true)->get();
        $rules = [
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
        ];

        // Add validation for each active language
        foreach ($languages as $language) {
            $rules["name.{$language->code}"] = ['required', 'string', 'max:255'];
        }

        $rules['is_active'] = ['sometimes', 'boolean'];

        return $rules;
    }
}

