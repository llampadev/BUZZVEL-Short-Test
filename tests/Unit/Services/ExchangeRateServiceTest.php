<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExchangeRateException;
use App\Services\ExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_rate_for_a_supported_currency(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'rates' => ['BRL' => 5.5, 'USD' => 1.1],
            ], 200),
        ]);

        $service = new ExchangeRateService;

        $rate = $service->getRate('BRL');

        $this->assertSame(5.5, $rate['rate']);
        $this->assertNotEmpty($rate['source']);
        $this->assertNotNull($rate['fetched_at']);
    }

    public function test_it_returns_a_rate_of_one_for_the_base_currency(): void
    {
        $service = new ExchangeRateService;

        $rate = $service->getRate('EUR');

        $this->assertSame(1.0, $rate['rate']);
    }

    public function test_it_throws_when_the_provider_is_unavailable(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([], 500),
        ]);

        $service = new ExchangeRateService;

        $this->expectException(ExchangeRateException::class);

        $service->getRate('BRL');
    }

    public function test_it_throws_when_currency_is_not_present_in_response(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'rates' => ['USD' => 1.1],
            ], 200),
        ]);

        $service = new ExchangeRateService;

        $this->expectException(ExchangeRateException::class);

        $service->getRate('BRL');
    }

    public function test_it_converts_an_amount_to_eur(): void
    {
        $service = new ExchangeRateService;

        $this->assertSame(100.0, $service->convertToEur(500, 5.0));
        $this->assertSame(90.91, $service->convertToEur(100, 1.1));
    }
}
