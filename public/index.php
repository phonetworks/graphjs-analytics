<?php declare(strict_types=1);

require "../vendor/autoload.php";

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

$last = $client->scard("analytics");
$cut = getenv("CUT");

if($last/$cut>1) {
    $house_analytics = (null !== getenv("HOUSE_ANALYTICS")) ? getenv("HOUSE_ANALYTICS") : [];
    $mdb = new \MeekroDB('localhost', 'username', 'password');
    $members = $client->smembers("analytics");
    $members_count = count($members);
    $client->spop("analytics", $members_count);
    foreach($members as $member)
    {
        if(
            !isset($member["public_id"]) ||
            in_array($member["public_id"], [])
        )
            continue;

        $mdb->insert("analytics", [
            "id"   => DB::sqleval("UUID_TO_BIN(%s)", $member["public_id"]),
            "tag"  => $member["tag"],
            "host" => $member["host"],
            "time" => $member["time"]
        ]);
    }
}

echo \json_encode(
    ["success"=>true]
);