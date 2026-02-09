<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SchoolTypeEnum;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition(): array
    {
        // Récupère les titres existants pour réduire les collisions avec l'unique index
        $existing = Type::query()->pluck('title')->all();
        $remaining = array_values(array_diff(SchoolTypeEnum::values(), $existing));
        $value = $remaining[0] ?? $this->faker->randomElement(SchoolTypeEnum::values());

        return [
            'title' => $value,
        ];
    }

    /**
     * Etat spécifique : type FORMEL
     */
    public function formel(): self
    {
        return $this->state(fn () => ['title' => SchoolTypeEnum::FORMEL->value]);
    }

    /**
     * Etat spécifique : type NON FORMEL
     */
    public function nonFormel(): self
    {
        return $this->state(fn () => ['title' => SchoolTypeEnum::NON_FORMEL->value]);
    }
}
