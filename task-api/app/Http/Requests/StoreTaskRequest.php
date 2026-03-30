<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth is handled by the route middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'status'      => ['nullable', 'string', Rule::in(['todo', 'in_progress', 'done'])],
            'priority'    => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist or does not belong to you.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }
}
