<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\OutputAccount;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OutputAccountFactory extends Factory
{
    protected $model = OutputAccount::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'account_type_id' => AccountType::factory(),
            'currency_id' => Currency::factory(),
            'exchange_rate_id' => ExchangeRate::factory(),

            'reference' => $this->faker->unique()->word,
            'amount_converted' => $this->faker->randomFloat(2, 10, 1000),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
        ];
    }
}
