<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Call;
use Carbon\Carbon;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Call::class, function (Faker $faker) {
    return [
        'central_id' => function () {
            return factory(User::class)->create()->central_id;
        },
        'extension' => $faker->randomNumber($numDigits = 3),
        'valid' => $faker->boolean(),
        'date' => Carbon::now(),
        'duration' => $faker->randomNumber(),
        'dialed_number' => $faker->numberBetween($min = 0000000, $max = 9999999),
        'incoming' => $faker->boolean(),
        'long_distance' => $faker->boolean(),
        'international' => $faker->boolean(),
        'local' => $faker->boolean(),
        'raw' => ''
    ];
});
