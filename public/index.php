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
                    "public_id" => null,
                    "tag"       => null,
                    "host"      => null,
                ]
                ),
            ["time"=>\time()]
        )
    )
);

$last = $client->scard("analytics");
$cut = getenv("CUT");

if($last/$cut>1) {
    $house_analytics = (\getenv("HOUSE_ANALYTICS")!==false) ? \explode(':', \getenv("HOUSE_ANALYTICS")) : [];
    error_log(print_r($house_analytics, true));
    $mdb = new \MeekroDB(
        \getenv("DB_HOST"), 
        \getenv('DB_USERNAME'), 
        \getenv("DB_PASSWORD"),
        \getenv("DB_DATABASE"),
        \getenv("DB_PORT")
    );
    error_log(print_r([ 
        \getenv("DB_HOST"), 
        \getenv('DB_USERNAME'), 
        \getenv("DB_PASSWORD"),
        \getenv("DB_DATABASE"),
        \getenv("DB_PORT")], true));
    $mdb->error_handler = false;
    $mdb->throw_exception_on_error = true;
    $members = $client->smembers("analytics");
    $members_count = count($members);
    $client->spop("analytics", $members_count);
    error_log(print_r($members, true));
    error_log("Member count:".$members_count);
    foreach($members as $member)
    {
        $member = json_decode($member, true);
        error_log(print_r($member, true));
        if(
            !isset($member["public_id"]) || // not set
            (\strlen($member["public_id"])!=36) || // not valid
            \in_array($member["public_id"], $house_analytics) // house account
        ) {
            error_log("skipping because ".isset($member["public_id"]). " - ".\strlen($member["public_id"])." - ".\in_array($member["public_id"], $house_analytics));
            continue;
        }
        try {
            $mdb->insert("analytics", [
                "id"   => $member["public_id"],
                "tag"  => $member["tag"],
                "host" => $member["host"],
                "time" => \MeekroDB::sqleval("FROM_UNIXTIME(%d)", $member["time"])
            ]);
        }
        catch(\Exception $e) {
            \error_log("There was some error with inserting data: ".print_r($member, true));
        }
    }
}

echo \json_encode(
    ["success"=>true]
);