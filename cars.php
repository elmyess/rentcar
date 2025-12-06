<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../models/Car.php';

$database = new Database();
$db = $database->getConnection();

$car = new Car($db);

// Déterminer si on veut les voitures featured ou toutes
$featured = isset($_GET['featured']) ? $_GET['featured'] : false;

if($featured) {
    $stmt = $car->readFeatured();
} else {
    $stmt = $car->read();
}

$num = $stmt->rowCount();

if($num > 0) {
    $cars_arr = array();
    $cars_arr["cars"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $car_item = array(
            "id" => $row['id'],
            "name" => $row['name'],
            "brand" => $row['brand'],
            "model" => $row['model'],
            "price_per_day" => $row['price_per_day'],
            "image" => $row['image'],
            "fuel_type" => $row['fuel_type'],
            "transmission" => $row['transmission'],
            "seats" => $row['seats']
        );
        array_push($cars_arr["cars"], $car_item);
    }
    
    http_response_code(200);
    echo json_encode($cars_arr);
} else {
    http_response_code(200);
    echo json_encode(array("cars" => array()));
}
?>