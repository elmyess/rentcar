<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Test de la classe Reservation<br>";

include_once 'config/database.php';
include_once 'models/Reservation.php';

$database = new Database();
$db = $database->getConnection();

echo "✅ Database connectée<br>";

// Test 1: Instanciation
try {
    $reservation = new Reservation($db);
    echo "✅ Classe Reservation instanciée<br>";
} catch (Error $e) {
    echo "❌ Erreur instanciation: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Méthodes
if (method_exists($reservation, 'create')) {
    echo "✅ Méthode create() existe<br>";
} else {
    echo "❌ Méthode create() n'existe pas<br>";
}

if (method_exists($reservation, 'isCarAvailable')) {
    echo "✅ Méthode isCarAvailable() existe<br>";
} else {
    echo "❌ Méthode isCarAvailable() n'existe pas<br>";
}

echo "🎉 Test terminé<br>";
?>