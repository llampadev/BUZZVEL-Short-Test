<?php

namespace App\Console\Commands;

use App\Models\PaymentRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payments:expire-pending')]
#[Description('Expire pending payment requests older than 48 hours')]
class ExpirePendingPaymentRequests extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = PaymentRequest::query()
            ->where('status', PaymentRequest::STATUS_PENDING)
            ->where('expires_at', '<=', now())
            ->update(['status' => PaymentRequest::STATUS_EXPIRED]);

        $this->info("Expired {$count} pending payment request(s).");

        return self::SUCCESS;
    }
}
