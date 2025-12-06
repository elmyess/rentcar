<?php
echo "<h2>🧪 Test des Chemins - Elayadi Prestige Car</h2>";

// Test 1: database.php
$db_path = 'config/database.php';
if (file_exists($db_path)) {
    echo "✅ database.php TROUVÉ<br>";
} else {
    echo "❌ database.php NON TROUVÉ (cherché: $db_path)<br>";
}

// Test 2: Reservation.php depuis back-end/
$reservation_path1 = 'models/Reservation.php';
if (file_exists($reservation_path1)) {
    echo "✅ models/Reservation.php TROUVÉ<br>";
} else {
    echo "❌ models/Reservation.php NON TROUVÉ (cherché: $reservation_path1)<br>";
}

// Test 3: Reservation.php depuis back-end/api/
$reservation_path2 = '../models/Reservation.php';
if (file_exists($reservation_path2)) {
    echo "✅ ../models/Reservation.php TROUVÉ<br>";
} else {
    echo "❌ ../models/Reservation.php NON TROUVÉ (cherché: $reservation_path2)<br>";
}

// Test 4: Dossier actuel
echo "<br><strong>Dossier actuel:</strong> " . __DIR__ . "<br>";

// Test 5: Structure des dossiers
echo "<br><strong>Structure autour:</strong><br>";
$items = scandir(__DIR__);
foreach ($items as $item) {
    if ($item != '.' && $item != '..') {
        $type = is_dir($item) ? '📁' : '📄';
        echo "$type $item<br>";
    }
}

// Test 6: Contenu du dossier models
$models_dir = __DIR__ . '/models';
if (is_dir($models_dir)) {
    echo "<br><strong>Contenu de models/:</strong><br>";
    $model_files = scandir($models_dir);
    foreach ($model_files as $file) {
        if ($file != '.' && $file != '..') {
            echo "📄 $file<br>";
        }
    }
} else {
    echo "<br>❌ Dossier models/ n'existe pas<br>";
}
?>