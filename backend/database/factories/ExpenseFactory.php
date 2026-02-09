<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'account_type_id' => AccountType::factory(),

            'currency_id' => Currency::factory(),
            'exchange_rate_id' => ExchangeRate::factory(),

            'expense_raison' => fake()->word(10),
            'beneficiary' => fake()->name(),
            'reference' => 'EXP-'.mb_strtoupper(fake()->unique()->bothify('###??')),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'amount_converted' => null,
            'description' => fake()->sentence(),
            'expense_date' => fake()->date(),
        ];
    }
}
