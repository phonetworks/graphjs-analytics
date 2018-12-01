<?php

require "vendor/autoload.php";

use Ramsey\Uuid\Uuid;

$faker = \Faker\Factory::create();

$words = $faker->words(7);
$urls = [
    $faker->url,$faker->url,$faker->url,$faker->url,$faker->url,$faker->url,$faker->url
];
$uuids = [
    $faker->uuid,$faker->uuid,$faker->uuid,$faker->uuid,$faker->uuid,$faker->uuid,$faker->uuid,
];

//eval(\Psy\sh());

for($i=0;$i<200; $i++) {
    echo "Query #".$i."\n";
    file_get_contents(
        "https://graphjs-analytics-test.herokuapp.com/index.php"
        . "?public_id=" . $uuids[rand(0,6)]
        . "&tag=graphjs-" . $words[rand(0, 6)]
        . "&host=" . $urls[rand(0, 6)]
    );
    //if($i!=0&&$i%10==0)
//        sleep(180);
    sleep(1);
}