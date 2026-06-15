<?php

namespace Database\Seeders;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = User::where('role', User::ROLE_EMPLOYEE)->get();

        foreach ($employees as $employee) {
            PaymentRequest::factory()->create([
                'user_id' => $employee->id,
                'currency' => $employee->currency,
            ]);

            PaymentRequest::factory()->approved()->create([
                'user_id' => $employee->id,
                'currency' => $employee->currency,
                'approved_by' => User::where('role', User::ROLE_FINANCE)->value('id'),
            ]);
        }

        PaymentRequest::factory()->expired()->create([
            'user_id' => $employees->first()->id,
            'currency' => $employees->first()->currency,
        ]);
    }
}
