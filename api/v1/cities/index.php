<?php

use App\Services\CityService;
use App\Utilities\CacheUtility;
use App\Utilities\Response;

include "../../../loader.php";

$token = getBearerToken();

if (!$token) {
    Response::respondAndDie(["TOKEN NOT FOUND!!!"], 404);
}

$user = isValidToken($token);

if (!$user) {
    Response::respondAndDie(["INVALID TOKEN!!!"], 401);
}

$request_body = json_decode(file_get_contents("php://input"), 1);
$cityService = new CityService();

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        $p_id = $_GET['province_id'] ?? null;

        if (!hasAccessToProvince($user, $p_id)) {
            Response::respondAndDie(["CAN'T ACCESS TO THE PROVINCE!!!"], 403);
        }

        CacheUtility::start();

        $page = $_GET['page'] ?? null;
        $pageSize = $_GET['pagesize'] ?? null;

        $request_data = [
            "province_id" => $p_id,
            "page" => $page,
            "pagesize" => $pageSize
        ];

        $response = $cityService->getCities($request_data);

        if (empty($response)) {
            Response::respondAndDie($response, 404);
        }

        echo Response::respond($response, 200);

        CacheUtility::end();
        break;
    case 'POST':
        $response = $cityService->createCity($request_body);

        if (!$response) {
            Response::RespondAndDie("Please Provide a Valid Body With Name And a Province ID", 406);
        }

        Response::RespondAndDie($response, 201);
        break;
    case 'PUT':
        [$city_id, $city_name] = [$request_body['city_id'], $request_body['name']];

        if (!is_numeric($city_id) || empty($city_name)) {
            Response::RespondAndDie(["INVALID REQUEST!!!"], 406);
        }

        $response = $cityService->updateCity($city_id, $city_name);

        if ($response == 0) {
            Response::RespondAndDie(["CITY NOT FOUND!!!"], 404);
        }

        Response::RespondAndDie($response, 200);
        break;
    case 'DELETE':
        $city_id = $_GET['city_id'];

        if (!is_numeric($city_id) || empty($city_id)) {
            Response::RespondAndDie(["INVALID REQUEST!!!"], 406);
        }

        $response = $cityService->removeCity($city_id);

        if ($response == 0) {
            Response::RespondAndDie(["CITY NOT FOUND!!!"], 404);
        }

        Response::RespondAndDie($response, 200);
        break;
    default:
        Response::respondAndDie(["INVALID REQUEST METHOD"], 405);
}
