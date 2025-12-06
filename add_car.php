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

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $price_per_day = $_POST['price_per_day'];
    $fuel_type = $_POST['fuel_type'];
    $transmission = $_POST['transmission'];
    $seats = $_POST['seats'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Gestion de l'upload d'image
    $image_path = '';
    if(isset($_FILES['car_image']) && $_FILES['car_image']['error'] == 0) {
        $upload_dir = '../../images/'; // Chemin vers le dossier images du projet

        // Créer le dossier s'il n'existe pas
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Générer un nom unique pour l'image
        $file_extension = pathinfo($_FILES['car_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('car_') . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;

        // Vérifier le type de fichier
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array(strtolower($file_extension), $allowed_types)) {
            if(move_uploaded_file($_FILES['car_image']['tmp_name'], $target_file)) {
                $image_path = 'images/' . $file_name;
            } else {
                $error = "Erreur lors de l'upload de l'image";
            }
        } else {
            $error = "Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.";
        }
    } else {
        $error = "Veuillez sélectionner une image pour la voiture";
    }

    if(empty($error)) {
        $query = "INSERT INTO cars (name, brand, model, price_per_day, fuel_type, transmission, seats, image, featured)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($query);
        if($stmt->execute([$name, $brand, $model, $price_per_day, $fuel_type, $transmission, $seats, $image_path, $featured])) {
            $message = "Voiture ajoutée avec succès";
            $_POST = array(); // Clear form
            // Rediriger vers le site principal pour voir la nouvelle voiture
            header("Location: ../../index.html?message=" . urlencode("Nouvelle voiture ajoutée: $name"));
            exit;
        } else {
            $error = "Erreur lors de l'ajout de la voiture";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une Voiture - Admin</title>
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
        }
        .btn:hover {
            background: #B8941F;
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
        <h1>Ajouter une Voiture</h1>
        
        <?php if($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nom de la voiture</label>
                    <input type="text" id="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="brand">Marque</label>
                    <input type="text" id="brand" name="brand" value="<?php echo $_POST['brand'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Modèle</label>
                    <input type="text" id="model" name="model" value="<?php echo $_POST['model'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price_per_day">Prix par jour (DH)</label>
                    <input type="number" id="price_per_day" name="price_per_day" step="0.01" value="<?php echo $_POST['price_per_day'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="fuel_type">Type de carburant</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="">Sélectionner</option>
                        <option value="Essence">Essence</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Hybride">Hybride</option>
                        <option value="Électrique">Électrique</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission" required>
                        <option value="">Sélectionner</option>
                        <option value="Manual">Manuelle</option>
                        <option value="Automatic">Automatique</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="seats">Nombre de places</label>
                    <input type="number" id="seats" name="seats" min="2" max="9" value="<?php echo $_POST['seats'] ?? '5'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="car_image">Photo de la voiture</label>
                    <input type="file" id="car_image" name="car_image" accept="image/*" required>
                    <small>Formats acceptés: JPG, PNG, GIF</small>
                    <div id="image-preview" style="margin-top: 10px; display: none;">
                        <img id="preview-img" src="" alt="Aperçu" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="featured" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                        <label for="featured">Voiture en vedette</label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Ajouter la Voiture</button>
                <a href="cars.php" style="margin-left: 1rem; color: #666;">Annuler</a>
            </form>
        </div>
    </div>

    <script>
        // Aperçu de l'image sélectionnée
        document.getElementById('car_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');

            if (file) {
                // Vérifier le type de fichier
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Veuillez sélectionner une image valide (JPG, PNG, GIF)');
                    e.target.value = '';
                    preview.style.display = 'none';
                    return;
                }

                // Vérifier la taille du fichier (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('L\'image ne doit pas dépasser 5MB');
                    e.target.value = '';
                    preview.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Validation du formulaire avant soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('car_image');
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Veuillez sélectionner une photo pour la voiture');
                return;
            }

            // Changer le texte du bouton pendant l'upload
            const submitBtn = document.querySelector('.btn[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Ajout en cours...';
            submitBtn.disabled = true;

            // Réactiver après un délai (au cas où)
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    </script>
</body>
</html>
