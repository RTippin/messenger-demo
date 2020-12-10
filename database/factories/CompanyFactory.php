<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use RTippin\Messenger\Facades\Messenger;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'company_email' => $this->faker->unique()->safeEmail,
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
        return $this->afterMaking(function (Company $company) {
            //
        })->afterCreating(function (Company $company) {
            Messenger::getProviderMessenger($company);
        });
    }
}
