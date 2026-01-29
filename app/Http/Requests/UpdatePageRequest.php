<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation (decode sections JSON).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('sections') && is_string($this->sections)) {
            $decoded = json_decode($this->sections, true);
            $this->merge(['sections' => is_array($decoded) ? $decoded : []]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'sections' => [
                'required',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_array($value) || !collect($value)->contains('type', 'title')) {
                        $fail(__('The page layout must contain at least one Title block (used for page slug).'));
                    }
                },
            ],
            'sections.*.id' => ['required', 'string', 'max:64'],
            'sections.*.type' => ['required', 'string', 'in:banner,title,description,inputs,send_email_form'],
            'sections.*.data' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        return $rules;
    }
}
