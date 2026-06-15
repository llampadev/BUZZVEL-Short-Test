<?php

namespace App\Http\Requests\PaymentRequest;

use App\Models\PaymentRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPaymentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in([
                PaymentRequest::STATUS_PENDING,
                PaymentRequest::STATUS_APPROVED,
                PaymentRequest::STATUS_REJECTED,
                PaymentRequest::STATUS_EXPIRED,
            ])],
        ];
    }
}
