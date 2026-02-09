<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeType>
 */
final class FeeTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = FeeType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Mensuel',
                'Trimestriel',
                'Annuel',
                'Périodique',
                'Circonstanciel',
            ]),
            'code' => $this->faker->unique()->lexify('type_????'),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
