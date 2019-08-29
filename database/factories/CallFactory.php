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
        'intranet_user_id' => $faker->randomNumber(),
        'stats_call_id' => $faker->randomNumber(),
        'valid' => $faker->boolean(),
        'dialed_number' => $faker->numberBetween($min = 0000000, $max = 9999999),
        'duration' => $faker->randomNumber(),
        'date' => Carbon::now(),
    ];
});
