<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    /**
     * Any authenticated user can create payment requests for themselves.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * The owner or a finance user can view a payment request.
     */
    public function view(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->isFinance() || $user->id === $paymentRequest->user_id;
    }

    /**
     * Only finance users can approve/reject pending payment requests.
     */
    public function review(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->isFinance() && $paymentRequest->isPending();
    }
}
