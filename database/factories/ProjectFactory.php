<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['fixed', 'hourly']);
        $budget = $type === 'fixed'
            ? fake()->randomFloat(2, 100, 10000)
            : fake()->randomFloat(2, 15, 150);

        $status = fake()->randomElement(['open', 'open', 'open', 'in_progress', 'closed']);

        return [
            'user_id' => User::factory()->client()->create(),
            'title' => fake()->sentence(fake()->numberBetween(4, 8)),
            'description' => fake()->paragraphs(fake()->numberBetween(3, 6), true),
            'type' => $type,
            'budget' => $budget,
            'delivery_date' => $status === 'open' || $status === 'in_progress'
                ? fake()->dateTimeBetween('now', '+3 months')
                : fake()->dateTimeBetween('-1 month', 'now'),
            'status' => $status,
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => null,
        ];
    }
 
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'budget' => fake()->randomFloat(2, 100, 10000),
        ]);
    }

    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hourly',
            'budget' => fake()->randomFloat(2, 15, 150),
        ]);
    }


    public function withBudget($min = 500, $max = 5000): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => fake()->randomFloat(2, $min, $max),
        ]);
    }


    public function withDeliveryDays($days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_date' => Carbon::now()->addDays($days),
        ]);
    }
}
