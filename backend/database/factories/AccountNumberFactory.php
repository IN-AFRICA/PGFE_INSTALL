<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AccountNumberFactory extends Factory
{
    protected $model = AccountNumber::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'class_account_id' => \App\Models\ClassAccount::factory(),
        ];
    }
}
