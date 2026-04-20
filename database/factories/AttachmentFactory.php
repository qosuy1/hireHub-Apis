<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $fileType = fake()->randomElement($fileTypes);
        $extension = match ($fileType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            default => 'jpg',
        };

        return [
            'file_name' => fake()->word() . '_' . fake()->uuid() . '.' . $extension,
            'file_path' => 'attachments/' . fake()->uuid() . '.' . $extension,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(50, 5000),
        ];
    }

    /**
     * Indicate the attachment is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png', 'image/gif']),
            'file_name' => fake()->word() . '_' . fake()->uuid() . '.jpg',
            'file_path' => 'attachments/' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Indicate the attachment is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => fake()->randomElement([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]),
            'file_name' => fake()->word() . '_' . fake()->uuid() . '.pdf',
            'file_path' => 'attachments/' . fake()->uuid() . '.pdf',
        ]);
    }
}
