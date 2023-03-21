<?php

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
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

//    $address = 'str. Haltei, Chișinău, Moldova';
//    $lat = 47.01045290;
//    $lng = 28.86381030;

    $address = 'Piusstraße 43, 50823 Köln, Deutschland';
    $lat = 50.94378497;
    $lng = 6.92361521;

    $newLat = $lat + rand(1,99) / 750 * (rand(0,1) ? 1 : -1);
    $newLng = $lng + rand(1,99) / 750 * (rand(0,1) ? 1 : -1);

    $backendService = app('\App\Services\BackendService');

    $heights = $backendService->getPredefinedHeights();
    $height = array_rand($heights);

    $weights = $backendService->getPredefinedWeights();
    $weight = array_rand($weights);

    $bodyTypes = $backendService->getPredefinedBodyTypes();
    $bodyType = array_rand($bodyTypes);

    $penisSizes = $backendService->getPredefinedPenisSizes();
    $penisSize = array_rand($penisSizes);

    $positionTypes = $backendService->getPredefinedPositionTypes();
    $positionType = array_rand($positionTypes);

    $hivTypes = $backendService->getPredefinedHivTypes();
    $hivType = array_rand($hivTypes);

    $drugsTypes = $backendService->getPredefinedDrugsTypes();
    $drugsType = array_rand($drugsTypes);

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
//        'password' => $password ?: $password = bcrypt('secret'),
        'password' => '$2y$10$Nx5xEP40CUs0LBw/8DlpN./gBjsClHET2cGg8hUnie4k9BjI1LflC',
        'dob' => '1962-07-11',
        'location_type' => 'manual',
        'remember_token' => str_random(10),
        'address' => $address,
        'lat' => $newLat,
        'lng' => $newLng,
        'show_age' => 'no',
        'unit_system' => 'metric',
        'gps_geom' => \DB::raw("POINT($newLng, $newLat)"),
        'height' => $height,
        'weight' => $weight,
        'body' => $bodyType,
        'penis' => $penisSize,
        'position' => $positionType,
        'hiv' => $hivType,
        'drugs' =>$drugsType
    ];
});
