<?php

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define("ROOT_PATH", dirname(__DIR__) . '/');

require_once ROOT_PATH . 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

$database_connection = (object)[
    "name" => $_ENV['DB_NAME'],
    "host" => $_ENV['DB_HOST'],
    "username" => $_ENV['DB_USER'],
    "password" => $_ENV['DB_PASS']
];

$dsn = "mysql:dbname=$database_connection->name;host=$database_connection->host";

try {
    $pdo = new PDO($dsn, $database_connection->username, $database_connection->password);
    $pdo->exec("set names utf8;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function isValidCity($data)
{
    if (empty($data['province_id']) or !is_numeric($data['province_id'])) {
        return false;
    }

    return !empty($data['name']);
}


function isValidProvince($data)
{
    global $pdo;
    $sql = "SELECT * FROM provinces WHERE id = :province_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['province_id' => $data['province_id']]);

    return (bool)$stmt->fetch(PDO::FETCH_OBJ);
}

function getCities($data = null)
{
    global $pdo;
    $province_id = $data['province_id'] ?? null;
    $page = $data['page'] ?? null;
    $pageSize = $data['pagesize'] ?? null;
    $limit = '';

    if (is_numeric($page) and is_numeric($pageSize)) {
        $start = ($page - 1) * $pageSize;
        $limit = " LIMIT $start,$pageSize";
    }

    $where = '';

    if (!is_null($province_id) and is_numeric($province_id)) {
        $where = "where province_id = $province_id";
    }

    $sql = "select * from cities $where $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

function addCity($data)
{
    global $pdo;

    if (!isValidCity($data)) {
        return false;
    }

    if (!isValidProvince($data)) {
        return false;
    }

    $sql = "INSERT INTO `cities` (`province_id`, `name`) VALUES (:province_id, :name);";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':province_id' => $data['province_id'], ':name' => $data['name']]);

    return $stmt->rowCount();
}

function changeCityName($city_id, $name)
{
    global $pdo;
    $sql = "UPDATE cities SET name = :name WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => $name, ':id' => $city_id]);

    return $stmt->rowCount();
}

function deleteCity($city_id)
{
    global $pdo;
    $sql = "DELETE FROM cities WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $city_id]);

    return $stmt->rowCount();
}

$users = [
    (object)['id' => 1, 'name' => 'user1', 'email' => 'a@b.com', 'role' => 'admin', 'allowed_provinces' => []],
    (object)['id' => 2, 'name' => 'user2', 'email' => 'b@c.com', 'role' => 'mayor', 'allowed_provinces' => [1, 2, 3, 4, 5]],
    (object)['id' => 3, 'name' => 'user3', 'email' => 'c@d.com', 'role' => 'governor', 'allowed_provinces' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
    (object)['id' => 4, 'name' => 'user4', 'email' => 'd@e.com', 'role' => 'president', 'allowed_provinces' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31]],
];

function getUserById($id)
{
    global $users;

    foreach ($users as $user) {
        if ($user->id == $id) {
            return $user;
        }
    }

    return null;
}

function getUserByEmail($email)
{
    global $users;

    foreach ($users as $user)
        if (strtolower($user->email) == strtolower($email)) {
            return $user;
        }

    return null;
}

function createApiToken($user)
{
    $payload = ["user_id" => $user->id];

    return JWT::encode($payload, JWT_KEY, JWT_ALG);
}

function isValidToken($jwt_token)
{
    try {
        $payload = JWT::decode($jwt_token, new Key(JWT_KEY, JWT_ALG));

        return getUserById($payload->user_id);
    } catch (Exception $e) {
        return false;
    }
}

function hasAccessToProvince($user, $province_id)
{
    return (in_array($user->role, ['governor', 'president']) || in_array($province_id, $user->allowed_provinces));
}

function getAuthorizationHeader()
{
    $headers = null;

    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    return $headers;
}

function getBearerToken()
{
    $headers = getAuthorizationHeader();

    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }

    return null;
}
