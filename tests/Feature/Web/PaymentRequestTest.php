<?php

namespace Tests\Feature\Web;

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

    public function test_employee_only_sees_their_own_requests_on_dashboard(): void
    {
        $employee = User::factory()->create();
        $other = User::factory()->create();

        $mine = PaymentRequest::factory()->for($employee)->create(['description' => 'My travel claim']);
        PaymentRequest::factory()->for($other)->create(['description' => 'Someone else']);

        $response = $this->actingAs($employee)->get('/dashboard');

        $response->assertOk()
            ->assertSee('My travel claim')
            ->assertDontSee('Someone else');
    }

    public function test_finance_sees_every_request_on_dashboard(): void
    {
        $finance = User::factory()->finance()->create();
        $employee = User::factory()->create();

        PaymentRequest::factory()->for($employee)->create(['description' => 'Employee request']);

        $response = $this->actingAs($finance)->get('/dashboard');

        $response->assertOk()->assertSee('Employee request');
    }

    public function test_user_can_view_the_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/payment-requests/create')
            ->assertOk()
            ->assertSee('New Payment Request');
    }

    public function test_user_can_create_a_payment_request(): void
    {
        $this->fakeExchangeRate(5.0);

        $user = User::factory()->create(['currency' => 'BRL']);

        $response = $this->actingAs($user)->post('/payment-requests', [
            'amount' => 500,
            'currency' => 'BRL',
            'description' => 'Client dinner',
        ]);

        $paymentRequest = PaymentRequest::first();

        $response->assertRedirect(route('payment-requests.show', $paymentRequest));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $user->id,
            'amount' => 500,
            'currency' => 'BRL',
            'exchange_rate' => 5.0,
            'amount_eur' => 100.0,
            'status' => 'pending',
        ]);
    }

    public function test_creation_validates_input(): void
    {
        $this->fakeExchangeRate();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payment-requests', [
            'amount' => -10,
            'currency' => 'XX',
        ]);

        $response->assertSessionHasErrors(['amount', 'currency']);
        $this->assertDatabaseCount('payment_requests', 0);
    }

    public function test_user_cannot_view_another_users_payment_request(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $paymentRequest = PaymentRequest::factory()->for($owner)->create();

        $this->actingAs($other)->get("/payment-requests/{$paymentRequest->id}")
            ->assertForbidden();
    }

    public function test_finance_can_approve_a_pending_request(): void
    {
        $finance = User::factory()->finance()->create();
        $employee = User::factory()->create();

        $paymentRequest = PaymentRequest::factory()->for($employee)->create();

        $response = $this->actingAs($finance)
            ->patch("/payment-requests/{$paymentRequest->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'status' => 'approved',
            'approved_by' => $finance->id,
        ]);
    }

    public function test_non_finance_user_cannot_approve_a_request(): void
    {
        $employee = User::factory()->create();
        $paymentRequest = PaymentRequest::factory()->for($employee)->create();

        $response = $this->actingAs($employee)
            ->patch("/payment-requests/{$paymentRequest->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_approving_an_already_reviewed_request_shows_a_conflict_message(): void
    {
        $finance = User::factory()->finance()->create();
        $employee = User::factory()->create();

        $paymentRequest = PaymentRequest::factory()->for($employee)->approved()->create();

        $response = $this->actingAs($finance)
            ->patch("/payment-requests/{$paymentRequest->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
