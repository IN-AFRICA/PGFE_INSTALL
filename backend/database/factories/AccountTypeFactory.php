<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AccountTypeFactory extends Factory
{
    protected $model = AccountType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'account_number_id' => \App\Models\AccountNumber::factory(),
            'school_id' => \App\Models\School::factory(),
            'academic_personal_id' => \App\Models\AcademicPersonal::factory(),
        ];
    }
}
