<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'email'   => ['required', 'email:rfc,dns', 'max:150'],
            'company' => ['nullable', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:100'],
            'budget'  => ['nullable', 'string', 'in:<5k,5k-15k,15k-50k,>50k'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Please provide your full name.',
            'name.min'         => 'Name must be at least 2 characters.',
            'email.required'   => 'A valid email address is required.',
            'email.email'      => 'Please enter a valid email address.',
            'message.required' => 'Please describe your project.',
            'message.min'      => 'Your message should be at least 20 characters.',
            'budget.in'        => 'Please select a valid budget range.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_address' => $this->ip(),
        ]);
    }

    // Add this to see validation errors
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'message' => 'Validation failed'
        ], 422));
    }
}
