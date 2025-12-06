<?php
// Inclure le fichier de configuration qui définit les variables
require_once 'config/database.php';

try {
    // Utilisez les constantes ou variables définies dans database.php
    $pdo = new PDO("mysql:host=localhost;dbname=elayadi_prestige_car", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie";
    
    // Test supplémentaire : lister les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>📊 Tables trouvées : " . implode(', ', $tables);
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage();
}
?>