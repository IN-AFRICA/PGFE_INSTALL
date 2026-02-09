<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeeType;
use App\Models\PaymentMotif;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMotif>
 */
final class PaymentMotifFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PaymentMotif::class;

    public function definition(): array
    {
        return [
            'fee_type_id' => FeeType::factory(),
            'name' => $this->faker->randomElement(['Janvier', 'Trimestre 1', 'Uniforme']),
            'code' => $this->faker->unique()->lexify('motif_????'),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
