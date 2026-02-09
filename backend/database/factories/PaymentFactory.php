<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentMotif;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $rate = $this->faker->randomFloat(4, 1500, 2500);
        $amountLocal = $this->faker->randomFloat(2, 10, 1000) * $rate;
        $remaining = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0, $amountLocal / 2) : 0;
        $status = $this->faker->randomElement(['pending', 'confirmed', 'refunded']);

        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'fee_id' => Fee::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'payment_motif_id' => PaymentMotif::factory(),
            'currency_id' => Currency::factory(),
            'exchange_rate_id' => ExchangeRate::factory(),
            'account_type_id' => AccountType::factory(),

            'amount' => round($amountLocal, 2),
            'remaining_amount' => round($remaining, 2),
            'reference' => mb_strtoupper('PAY-'.$this->faker->uuid),
            'details' => $this->faker->sentence(),
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),

            'status' => $status,
            'confirmed_at' => now(),
            'refunded_at' => $status === 'refunded' ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
        ];
    }
}
