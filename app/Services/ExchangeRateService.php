<?php

namespace App\Services;

use App\Exceptions\ExchangeRateException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    /**
     * Fetch the EUR -> $currency exchange rate.
     *
     * @return array{rate: float, source: string, fetched_at: Carbon}
     */
    public function getRate(string $currency): array
    {
        $currency = strtoupper($currency);
        $baseCurrency = config('currencies.exchange_rate.base_currency');

        if ($currency === $baseCurrency) {
            return [
                'rate' => 1.0,
                'source' => $this->source(),
                'fetched_at' => now(),
            ];
        }

        $rates = $this->fetchRates();

        if (! isset($rates[$currency])) {
            throw ExchangeRateException::unavailable($currency, 'currency not present in provider response');
        }

        return [
            'rate' => (float) $rates[$currency],
            'source' => $this->source(),
            'fetched_at' => now(),
        ];
    }

    /**
     * Convert an amount in local currency to EUR using the given EUR -> currency rate.
     */
    public function convertToEur(float $amount, float $rate): float
    {
        return round($amount / $rate, 2);
    }

    /**
     * @return array<string, float>
     */
    protected function fetchRates(): array
    {
        $baseCurrency = config('currencies.exchange_rate.base_currency');
        $ttl = (int) config('currencies.exchange_rate.cache_ttl');

        return Cache::remember("exchange-rates:{$baseCurrency}", $ttl, function () use ($baseCurrency) {
            $url = rtrim(config('currencies.exchange_rate.base_url'), '/')."/{$baseCurrency}";

            $response = Http::timeout(10)->get($url);

            if (! $response->successful()) {
                throw ExchangeRateException::unavailable($baseCurrency, "HTTP {$response->status()}");
            }

            $data = $response->json();

            if (($data['result'] ?? null) !== 'success' || empty($data['rates'])) {
                throw ExchangeRateException::unavailable($baseCurrency, 'unexpected provider response');
            }

            return $data['rates'];
        });
    }

    protected function source(): string
    {
        return config('currencies.exchange_rate.base_url');
    }
}
