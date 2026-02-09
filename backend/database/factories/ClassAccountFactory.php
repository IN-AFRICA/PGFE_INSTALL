<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClassAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ClassAccountFactory extends Factory
{
    protected $model = ClassAccount::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
        ];
    }
}
