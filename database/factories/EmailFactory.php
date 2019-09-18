<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Email;
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

$factory->define(Email::class, function (Faker $faker) {
    return [
        'central_id' => function () {
            return factory(User::class)->create()->central_id;
        },
        'walter_email_id' => rand(1, 100),
        'date' => Carbon::now(),
        'details' => '<p>Body of email</p>',
        'action' => rand(1, 2),
        'participant_email' => $faker->safeEmail(),
        'user_email' => $faker->safeEmail(),
    ];
});
