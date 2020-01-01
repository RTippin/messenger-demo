<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('Messenger1!'),
        'active' => 1,
        'remember_token' => null
    ];
});

$factory->define(App\Models\User\UserInfo::class, function (Faker $faker) {
    return [
        'picture' => null
//        'picture' => $faker->image(storage_path('/'),640,480, 'people', false, true)
    ];
});



$factory->define(App\Models\Messages\Thread::class, function (Faker $faker) {
    return [
        'ttype' => 2,
        'subject' => $faker->company,
        'image' => rand(1,5).'.png'
    ];
});

$factory->define(App\Models\Messages\Message::class, function (Faker $faker) {
    return [
        'mtype' => 0,
        'body' => $faker->realText(rand(10, 200), rand(1,4))
    ];
});
