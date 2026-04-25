<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatusEnum;
use App\Models\Freelancer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Freelancer>
 */
class FreelancerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->freelancer()->create(),
            'phone' => fake()->unique()->phoneNumber(),
            'bio' => fake()->paragraphs(3, true),
            'hourly_rate' => fake()->randomFloat(2, 15, 150),
            'avatar' => fake()->boolean(80) ? 'avatars/' . fake()->uuid() . '.jpg' : null,
            'portfolio_links' => $this->generatePortfolioLinks(),
            'availability_status' => fake()->randomElement(AvailabilityStatusEnum::getValues()),
            'average_rating' => fake()->randomFloat(1, 0, 5),
        ];
    }

    /**
     * Generate portfolio links.
     */
    private function generatePortfolioLinks(): ?array
    {
        if (fake()->boolean(30)) {
            return null;
        }

        $links = [];
        $platforms = ['github', 'linkedin', 'behance', 'dribbble', 'personal_website'];
        $count = fake()->numberBetween(1, 3);

        for ($i = 0; $i < $count; $i++) {
            $platform = fake()->randomElement($platforms);
            $links[$platform] = fake()->url();
        }

        return $links;
    }


    /**
     * Indicate the freelancer is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => AvailabilityStatusEnum::AVAILABLE->value,
        ]);
    }

    /**
     * Indicate the freelancer is busy.
     */
    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => AvailabilityStatusEnum::BUSY->value,
        ]);
    }

    /**
     * Indicate the freelancer is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => AvailabilityStatusEnum::UNAVAILABLE->value,
        ]);
    }

    /**
     * Indicate the freelancer has a high rating.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_rating' => fake()->randomFloat(1, 4.0, 5.0),
        ]);
    }

    /**
     * Indicate the freelancer has a specific hourly rate range.
     */
    public function withHourlyRate($min = 50, $max = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'hourly_rate' => fake()->randomFloat(2, $min, $max),
        ]);
    }
}
