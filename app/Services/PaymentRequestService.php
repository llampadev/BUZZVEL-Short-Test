<?php

namespace App\Services;

use App\Exceptions\ExchangeRateException;
use App\Exceptions\PaymentRequestReviewException;
use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestService
{
    public function __construct(
        protected ExchangeRateService $exchangeRateService,
    ) {}

    /**
     * Create a payment request for the given user, fetching and storing the
     * EUR exchange rate immutably at creation time.
     *
     * @param  array{amount: float|string, currency: string, description?: string|null}  $data
     *
     * @throws ExchangeRateException
     */
    public function create(User $user, array $data): PaymentRequest
    {
        $currency = strtoupper($data['currency']);
        $rate = $this->exchangeRateService->getRate($currency);
        $amountEur = $this->exchangeRateService->convertToEur((float) $data['amount'], $rate['rate']);

        return PaymentRequest::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'currency' => $currency,
            'exchange_rate' => $rate['rate'],
            'exchange_rate_source' => $rate['source'],
            'exchange_rate_fetched_at' => $rate['fetched_at'],
            'amount_eur' => $amountEur,
            'description' => $data['description'] ?? null,
            'status' => PaymentRequest::STATUS_PENDING,
            'expires_at' => now()->addHours(PaymentRequest::EXPIRATION_HOURS),
        ]);
    }

    /**
     * Approve or reject a pending payment request.
     *
     * @throws PaymentRequestReviewException
     */
    public function review(PaymentRequest $paymentRequest, User $actor, string $status): PaymentRequest
    {
        if (! $actor->isFinance()) {
            throw PaymentRequestReviewException::forbidden();
        }

        if (! $paymentRequest->isPending()) {
            throw PaymentRequestReviewException::alreadyReviewed($paymentRequest->status);
        }

        $paymentRequest->update([
            'status' => $status,
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $paymentRequest;
    }
}
