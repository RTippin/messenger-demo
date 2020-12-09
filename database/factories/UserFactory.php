<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use RTippin\Messenger\Facades\Messenger;

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
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'demo' => true,
            'password' => '$2y$10$rb4NakT8uw00mOPSUaaxMe4Ogy5ja8PUIgkdMhQQxa.apOO8wTI4a', // messenger
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (User $user) {
            //
        })->afterCreating(function (User $user) {
            Messenger::getProviderMessenger($user);
        });
    }
}
