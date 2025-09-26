<?php

use App\Utilities\Response;

include "../loader.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    Response::respondAndDie(["INVALID REQUEST METHOD!!!"], 405);
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (empty($email)) {
    Response::respondAndDie(["EMAIL IS REQUIRED!!!"], 400);
}

$user = getUserByEmail($email);

if (is_null($user)) {
    Response::respondAndDie("USER NOT FOUND!!!", 404);
}

$jwt = createApiToken($user);

Response::respondAndDie(["token" => $jwt, "user" => $user->name], 200);
