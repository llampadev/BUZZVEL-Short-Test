<?php

namespace App\Exceptions;

use Exception;

class PaymentRequestReviewException extends Exception
{
    public function __construct(string $message, public readonly int $statusCode)
    {
        parent::__construct($message);
    }

    public static function forbidden(): self
    {
        return new self('Only finance users can review payment requests.', 403);
    }

    public static function alreadyReviewed(string $status): self
    {
        return new self(
            "This payment request has already been {$status} and can no longer be reviewed.",
            409,
        );
    }
}
