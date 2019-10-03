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
        'international' => $international = $faker->boolean(),
        'dialed_number' => $international
            ? (int) "11{$faker->numberBetween($min = 111111111111,$max = 999999999999)}"
            : $faker->numberBetween($min = 11111111111, $max = 19999999999),
        'concatenated_number' => $faker->numberBetween($min = 1111111111, $max = 9999999999),
        'duration' => $faker->numberBetween($min = 1, $max = 9000),
        'date' => Carbon::now(),
    ];
});

$factory->state(Call::class, 'international', function (Faker $faker) {
    return [
        'international' => true,
        'dialed_number' => (int) "11{$faker->numberBetween($min = 111111111111,$max = 999999999999)}"
    ];
});

$factory->state(Call::class, 'national', function (Faker $faker) {
    return [
        'international' => false,
        'dialed_number' => $faker->numberBetween($min = 11111111111, $max = 19999999999)
    ];
});
