<?php

namespace Tests\Feature\PaymentRequests;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeExchangeRate(float $rate = 5.0): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'rates' => [
                    'BRL' => $rate,
                    'USD' => 1.1,
                    'GBP' => 0.85,
                ],
            ], 200),
        ]);
    }

    public function test_a_user_can_create_a_payment_request_with_converted_amount(): void
    {
        $this->fakeExchangeRate(5.0);

        $user = User::factory()->create(['currency' => 'BRL']);

        $response = $this->actingAs($user)->postJson('/api/payment-requests', [
            'amount' => 500,
            'currency' => 'BRL',
            'description' => 'Client dinner',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount', 500)
            ->assertJsonPath('data.currency', 'BRL')
            ->assertJsonPath('data.exchange_rate', 5)
            ->assertJsonPath('data.amount_eur', 100)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $user->id,
            'amount' => 500,
            'currency' => 'BRL',
            'exchange_rate' => 5.0,
            'amount_eur' => 100.0,
            'status' => 'pending',
        ]);
    }

    public function test_creation_requires_valid_data(): void
    {
        $this->fakeExchangeRate();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payment-requests', [
            'amount' => -10,
            'currency' => 'XX',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'currency']);
    }

    public function test_returns_service_unavailable_when_exchange_rate_provider_fails(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([], 500),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payment-requests', [
            'amount' => 100,
            'currency' => 'USD',
        ]);

        $response->assertStatus(503);
        $this->assertDatabaseCount('payment_requests', 0);
    }

    public function test_a_user_can_only_list_their_own_payment_requests(): void
    {
        $this->fakeExchangeRate();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        PaymentRequest::factory()->for($userA)->create();
        PaymentRequest::factory()->for($userB)->create();

        $response = $this->actingAs($userA)->getJson('/api/payment-requests');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_finance_user_can_list_all_payment_requests(): void
    {
        $finance = User::factory()->finance()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        PaymentRequest::factory()->for($userA)->create();
        PaymentRequest::factory()->for($userB)->create();

        $response = $this->actingAs($finance)->getJson('/api/payment-requests');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_list_can_be_filtered_by_status(): void
    {
        $finance = User::factory()->finance()->create();

        PaymentRequest::factory()->for(User::factory())->create(['status' => PaymentRequest::STATUS_PENDING]);
        PaymentRequest::factory()->for(User::factory())->approved()->create();

        $response = $this->actingAs($finance)->getJson('/api/payment-requests?status=approved');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('approved', $response->json('data.0.status'));
    }

    public function test_a_user_cannot_view_another_users_payment_request(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $paymentRequest = PaymentRequest::factory()->for($owner)->create();

        $response = $this->actingAs($other)->getJson("/api/payment-requests/{$paymentRequest->id}");

        $response->assertStatus(403);
    }

    public function test_finance_can_approve_a_pending_payment_request(): void
    {
        $finance = User::factory()->finance()->create();
        $paymentRequest = PaymentRequest::factory()->for(User::factory())->create();

        $response = $this->actingAs($finance)->patchJson("/api/payment-requests/{$paymentRequest->id}/approve");

        $response->assertOk()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'status' => 'approved',
            'approved_by' => $finance->id,
        ]);
    }

    public function test_finance_can_reject_a_pending_payment_request(): void
    {
        $finance = User::factory()->finance()->create();
        $paymentRequest = PaymentRequest::factory()->for(User::factory())->create();

        $response = $this->actingAs($finance)->patchJson("/api/payment-requests/{$paymentRequest->id}/reject");

        $response->assertOk()->assertJsonPath('data.status', 'rejected');
    }

    public function test_a_non_finance_user_cannot_approve_payment_requests(): void
    {
        $employee = User::factory()->create();
        $paymentRequest = PaymentRequest::factory()->for(User::factory())->create();

        $response = $this->actingAs($employee)->patchJson("/api/payment-requests/{$paymentRequest->id}/approve");

        $response->assertStatus(403);
    }

    public function test_an_already_reviewed_payment_request_cannot_be_reviewed_again(): void
    {
        $finance = User::factory()->finance()->create();
        $paymentRequest = PaymentRequest::factory()->for(User::factory())->approved()->create();

        $response = $this->actingAs($finance)->patchJson("/api/payment-requests/{$paymentRequest->id}/approve");

        $response->assertStatus(409);
    }
}
