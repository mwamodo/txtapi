<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SendTextMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'message' => ['required', 'string', 'min:1', 'max:160'],
            'key' => ['required', 'string', 'max:255'],
            'sender' => ['sometimes', 'string', 'max:11'],
            'replyWebhookUrl' => ['sometimes', 'url'],
            'webhookData' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number format is invalid',
            'message.required' => 'Message is required',
            'message.min' => 'Message cannot be empty',
            'message.max' => 'Message cannot exceed 160 characters',
            'key.required' => 'API key is required',
            'replyWebhookUrl.url' => 'Webhook URL must be a valid URL',
            'webhookData.max' => 'Webhook data cannot exceed 100 characters',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = response()->json([
            'success' => false,
            'error' => $this->getErrorCode($validator),
            'message' => $validator->errors()->first(),
        ], 400);

        throw new ValidationException($validator, $response);
    }

    private function getErrorCode(Validator $validator): string
    {
        foreach (['phone' => 'invalid_phone', 'message' => 'message_required_or_empty', 'key' => 'invalid_key'] as $field => $code) {
            if ($validator->errors()->has($field)) {
                return $code;
            }
        }
        return 'validation_error';
    }
}
