<?php

namespace Database\Factories;

use App\Models\Freelancer;
use App\Models\Offer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $project = Project::inRandomOrder()->first() ?? Project::factory()->open()->create();
        $freelancer = User::factory()->freelancer()->verified()->create();

        return [
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'cover_letter' => fake()->paragraphs(fake()->numberBetween(3, 5), true),
            'amount' => fake()->boolean(85)
                ? fake()->randomFloat(2, $project->budget * 0.8, $project->budget * 1.2)
                : null,
            'status' => fake()->randomElement(['pending', 'pending', 'pending', 'accepted', 'rejected']),
            'delevery_time' => fake()->numberBetween(7, 90),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => null,
        ];
    }

    /**
     * Indicate the offer is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate the offer is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    /**
     * Indicate the offer is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Set a specific amount range.
     */
    public function withAmount($min = 500, $max = 5000): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, $min, $max),
        ]);
    }

    /**
     * Set delivery time.
     */
    public function withDeliveryTime($days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'delevery_time' => $days,
        ]);
    }
}
