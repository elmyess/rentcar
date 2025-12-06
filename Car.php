<?php
class Car {
    private $conn;
    private $table_name = "cars";

    public $id;
    public $name;
    public $brand;
    public $model;
    public $price_per_day;
    public $image;
    public $available;
    public $fuel_type;
    public $transmission;
    public $seats;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE available = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readFeatured() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE available = 1 AND featured = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>