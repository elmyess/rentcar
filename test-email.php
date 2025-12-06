<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Test de la classe Email<br>";

include_once 'config/database.php';
include_once 'models/Email.php';

$database = new Database();
$db = $database->getConnection();

echo "✅ Database connectée<br>";

// Test 1: Instanciation
try {
    $email = new Email($db);
    echo "✅ Classe Email instanciée<br>";
} catch (Error $e) {
    echo "❌ Erreur instanciation: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Méthodes
if (method_exists($email, 'sendReservationConfirmation')) {
    echo "✅ Méthode sendReservationConfirmation() existe<br>";
} else {
    echo "❌ Méthode sendReservationConfirmation() n'existe pas<br>";
}

echo "🎉 Test Email terminé<br>";
?>