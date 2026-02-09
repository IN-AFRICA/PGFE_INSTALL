<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'type_id' => \App\Models\Type::factory(),
            'title' => $this->faker->words(3, true),
            'path' => $this->faker->words(3, true),
        ];
    }
}
