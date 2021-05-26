<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RTippin\Messenger\Models\Messenger;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'demo' => true,
            'admin' => false,
            'password' => '$2y$10$rb4NakT8uw00mOPSUaaxMe4Ogy5ja8PUIgkdMhQQxa.apOO8wTI4a', // messenger
        ];
    }

    /**
     * Indicate user is admin.
     *
     * @return Factory
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'admin' => true,
                'demo' => false,
            ];
        });
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): self
    {
        return $this->afterCreating(function (User $user) {
            Messenger::factory()->owner($user)->create();
        });
    }
}
