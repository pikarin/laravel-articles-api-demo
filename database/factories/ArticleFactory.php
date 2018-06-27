<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Article::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(10),
        'body' => $faker->paragraph(26),
        'created_at' => Carbon::now()->addSeconds(Carbon::now()->micro),
    ];
});
