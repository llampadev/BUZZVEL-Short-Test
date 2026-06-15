<?php

namespace Tests\Unit\Console;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirePendingPaymentRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_pending_payment_requests_older_than_48_hours(): void
    {
        $user = User::factory()->create();

        $expired = PaymentRequest::factory()->for($user)->create([
            'status' => PaymentRequest::STATUS_PENDING,
            'expires_at' => now()->subHour(),
        ]);

        $stillPending = PaymentRequest::factory()->for($user)->create([
            'status' => PaymentRequest::STATUS_PENDING,
            'expires_at' => now()->addHours(10),
        ]);

        $alreadyApproved = PaymentRequest::factory()->for($user)->approved()->create([
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('payments:expire-pending')->assertSuccessful();

        $this->assertSame(PaymentRequest::STATUS_EXPIRED, $expired->refresh()->status);
        $this->assertSame(PaymentRequest::STATUS_PENDING, $stillPending->refresh()->status);
        $this->assertSame(PaymentRequest::STATUS_APPROVED, $alreadyApproved->refresh()->status);
    }
}
