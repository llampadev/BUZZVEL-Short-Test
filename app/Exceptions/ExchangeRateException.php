<?php

namespace App\Exceptions;

use Exception;

class ExchangeRateException extends Exception
{
    public static function unavailable(string $currency, ?string $reason = null): self
    {
        $message = "Unable to fetch exchange rate for currency [{$currency}].";

        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return new self($message);
    }
}
