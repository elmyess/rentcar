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
$error = '';

// Récupérer les données de la voiture
$car = null;
if(isset($_GET['id'])) {
    $car_id = $_GET['id'];
    $query = "SELECT * FROM cars WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
}

if(!$car) {
    header("Location: cars.php");
    exit;
}

// Mettre à jour la voiture
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $price_per_day = $_POST['price_per_day'];
    $fuel_type = $_POST['fuel_type'];
    $transmission = $_POST['transmission'];
    $seats = $_POST['seats'];
    $image = $_POST['image'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    $query = "UPDATE cars SET name=?, brand=?, model=?, price_per_day=?, fuel_type=?, transmission=?, seats=?, image=?, featured=? WHERE id=?";
    
    $stmt = $db->prepare($query);
    if($stmt->execute([$name, $brand, $model, $price_per_day, $fuel_type, $transmission, $seats, $image, $featured, $car_id])) {
        $message = "Voiture modifiée avec succès";
        // Recharger les données
        $stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Erreur lors de la modification";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier la Voiture - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
        }
        .menu {
            background: #0A2540;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background 0.3s ease;
        }
        .menu a:hover {
            background: #D4AF37;
        }
        .container {
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .container h1 {
            font-family: 'Playfair Display', serif;
            color: #0A2540;
            margin-bottom: 2rem;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #0A2540;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #D4AF37;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-group input {
            width: auto;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: #D4AF37;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-right: 1rem;
        }
        .btn:hover {
            background: #B8941F;
        }
        .btn-cancel {
            background: #6c757d;
        }
        .btn-cancel:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="menu">
        <a href="index.php">Tableau de bord</a>
        <a href="cars.php">Gérer Voitures</a>
        <a href="reservations.php">Réservations</a>
        <a href="logout.php">Déconnexion</a>
    </div>

    <div class="container">
        <h1>Modifier la Voiture</h1>
        
        <?php if($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nom de la voiture</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="brand">Marque</label>
                    <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Modèle</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price_per_day">Prix par jour (DH)</label>
                    <input type="number" id="price_per_day" name="price_per_day" step="0.01" value="<?php echo $car['price_per_day']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="fuel_type">Type de carburant</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="">Sélectionner</option>
                        <option value="Essence" <?php echo $car['fuel_type'] == 'Essence' ? 'selected' : ''; ?>>Essence</option>
                        <option value="Diesel" <?php echo $car['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Hybride" <?php echo $car['fuel_type'] == 'Hybride' ? 'selected' : ''; ?>>Hybride</option>
                        <option value="Électrique" <?php echo $car['fuel_type'] == 'Électrique' ? 'selected' : ''; ?>>Électrique</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission" required>
                        <option value="">Sélectionner</option>
                        <option value="Manual" <?php echo $car['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manuelle</option>
                        <option value="Automatic" <?php echo $car['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatique</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="seats">Nombre de places</label>
                    <input type="number" id="seats" name="seats" min="2" max="9" value="<?php echo $car['seats']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Image (chemin relatif)</label>
                    <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($car['image']); ?>" required>
                    <small>Ex: images/ma-voiture.jpg</small>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="featured" name="featured" value="1" <?php echo $car['featured'] ? 'checked' : ''; ?>>
                        <label for="featured">Voiture en vedette</label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Modifier la Voiture</button>
                <a href="cars.php" class="btn btn-cancel">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>