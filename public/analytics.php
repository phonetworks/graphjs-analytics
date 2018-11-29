<?php declare(strict_types=1);

require "vendor/autoload.php";

\header("Access-Control-Allow-Origin: *");
\header("Access-Control-Allow-Headers: Content-Type,*");
\header("Access-Control-Allow-Methods: POST,GET,OPTIONS,PUT,DELETE");
\header("Content-Type: application/json");

$client = new \Predis\Client(\getenv("REDISCLOUD_URL"));
$client->sadd(
    "analytics", 
    \json_encode(
        \array_merge(
            \array_intersect_key(
                $_REQUEST, [
                    "public_id"=>null,
                    "tag"=>null,
                    "host"=>null,
                ]
                ),
            ["time"=>\time()]
        )
    )
);

echo \json_encode(
    ["success"=>true]
);