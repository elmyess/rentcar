<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $car_id;
    public $full_name;
    public $email;
    public $phone;
    public $pickup_location;
    public $pickup_date;
    public $return_date;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET car_id=:car_id, full_name=:full_name, email=:email, 
                     phone=:phone, pickup_location=:pickup_location, 
                     pickup_date=:pickup_date, return_date=:return_date,
                     status='pending', created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        // Nettoyer et valider les données
        $this->car_id = htmlspecialchars(strip_tags($this->car_id));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->pickup_location = htmlspecialchars(strip_tags($this->pickup_location));
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->return_date = htmlspecialchars(strip_tags($this->return_date));

        // Liaison des paramètres
        $stmt->bindParam(":car_id", $this->car_id);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":pickup_location", $this->pickup_location);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":return_date", $this->return_date);

        if($stmt->execute()) {
            return true;
        }
        
        // Afficher l'erreur SQL pour debug
        error_log("Erreur SQL Reservation: " . implode(", ", $stmt->errorInfo()));
        return false;
    }

    public function readAll() {
        $query = "SELECT r.*, c.name as car_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT r.*, c.name as car_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.id = ? 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->car_id = $row['car_id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->pickup_location = $row['pickup_location'];
            $this->pickup_date = $row['pickup_date'];
            $this->return_date = $row['return_date'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
        }
        
        return $row;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Compter les réservations totales
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Compter les réservations par statut
    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Vérifier si une voiture est disponible pour une période
    public function isCarAvailable($car_id, $pickup_date, $return_date, $exclude_reservation_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE car_id = ? 
                  AND status IN ('pending', 'confirmed')
                  AND ((pickup_date BETWEEN ? AND ?) 
                       OR (return_date BETWEEN ? AND ?) 
                       OR (pickup_date <= ? AND return_date >= ?))";
        
        if($exclude_reservation_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if($exclude_reservation_id) {
            $stmt->execute([$car_id, $pickup_date, $return_date, $pickup_date, $return_date, $pickup_date, $return_date, $exclude_reservation_id]);
        } else {
            $stmt->execute([$car_id, $pickup_date, $return_date, $pickup_date, $return_date, $pickup_date, $return_date]);
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }

    // Obtenir les réservations par statut
    public function readByStatus($status) {
        $query = "SELECT r.*, c.name as car_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.status = ? 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        return $stmt;
    }

    // Obtenir les réservations d'une voiture spécifique
    public function readByCar($car_id) {
        $query = "SELECT r.*, c.name as car_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.car_id = ? 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$car_id]);
        return $stmt;
    }

    // Mettre à jour une réservation complète
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET car_id=:car_id, full_name=:full_name, email=:email, 
                      phone=:phone, pickup_location=:pickup_location, 
                      pickup_date=:pickup_date, return_date=:return_date,
                      status=:status
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->car_id = htmlspecialchars(strip_tags($this->car_id));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->pickup_location = htmlspecialchars(strip_tags($this->pickup_location));
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->return_date = htmlspecialchars(strip_tags($this->return_date));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Liaison des paramètres
        $stmt->bindParam(":car_id", $this->car_id);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":pickup_location", $this->pickup_location);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":return_date", $this->return_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>