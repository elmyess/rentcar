<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Mapping of incorrect paths to correct paths
$image_fixes = [
    'images/clio5.png' => 'images/Clio5.png',
    'images/renault.png' => 'images/Renault.png',
    // Add more if needed
];

// Update each incorrect path
foreach ($image_fixes as $old_path => $new_path) {
    $query = "UPDATE cars SET image = ? WHERE image = ?";
    $stmt = $db->prepare($query);
    if($stmt->execute([$new_path, $old_path])) {
        $message .= "Updated $old_path to $new_path<br>";
    }
}

$message .= "Image paths updated successfully!";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix Image Paths - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Fix Image Paths</h1>
    <div class="success"><?php echo $message; ?></div>
    <br>
    <a href="cars.php">Back to Cars Management</a>
</body>
</html>
