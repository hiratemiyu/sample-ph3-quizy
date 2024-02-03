<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\BigQuestion; // モデルクラスを正しく指定
use Faker\Generator as Faker;

$factory->define(App\BigQuestion::class, function (Faker $faker) {
    return [
        'name' => $faker->name, // ここでFakerを使用して名前を生成
    ];
});

