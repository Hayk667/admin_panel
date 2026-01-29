<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class UpdatePostRequest extends FormRequest
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
        $postId = $this->route('post')?->id ?? $this->route('id');
        $languages = Language::where('is_active', true)->get();
        
        $rules = [
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('posts', 'slug')->ignore($postId)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        // Add validation for each active language
        foreach ($languages as $language) {
            $rules["title.{$language->code}"] = ['required', 'string', 'max:255'];
            $rules["content.{$language->code}"] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif, webp.',
            'image.max' => 'The image may not be greater than 2MB.',
        ];
    }
}

