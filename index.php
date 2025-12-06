 <?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Inclure les fichiers avec des chemins absolus
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/Reservation.php';

$database = new Database();
$db = $database->getConnection();

// Initialiser les compteurs à 0 en cas d'erreur
$cars_count = 0;
$reservations_count = 0;
$pending_count = 0;
$confirmed_count = 0;

try {
    $car = new Car($db);
    $reservation = new Reservation($db);
    
    // Compter les voitures et réservations
    $cars_count = $car->read()->rowCount();
    $reservations_count = $reservation->countAll();
    $pending_count = $reservation->countByStatus('pending');
    $confirmed_count = $reservation->countByStatus('confirmed');
    
} catch (Exception $e) {
    // En cas d'erreur, on garde les valeurs à 0
    error_log("Erreur dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord administrateur - Elayadi Prestige Car</title>
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
            background: #000000;
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
            background: #DC143C;
        }
        .dashboard {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .dashboard h1 {
            font-family: 'Playfair Display', serif;
            color: #000000;
            margin-bottom: 2rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #D4AF37;
        }
        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: #DC143C;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #B22222;
        }
        .recent-reservations {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .reservation-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .reservation-item:last-child {
            border-bottom: none;
        }
        .status-pending { color: #ffc107; font-weight: 600; }
        .status-confirmed { color: #28a745; font-weight: 600; }
        .status-cancelled { color: #dc3545; font-weight: 600; }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="menu">
        <a href="index.php">Tableau de bord</a>
        <a href="cars.php">Gérer Voitures</a>
        <a href="reservations.php">Réservations</a>
        <a href="logout.php">Déconnexion</a>
        <span style="margin-left: auto;">Connecté en tant que: <?php echo $_SESSION['admin_email']; ?></span>
    </div>
    
    <div class="dashboard">
        <h1>Tableau de Bord Administrateur</h1>
        
        <?php if (isset($e)): ?>
            <div class="error-message">
                <strong>Erreur de connexion à la base de données:</strong><br>
                Vérifiez que la base de données "elayadi_prestige_car" existe et que les tables sont créées.
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Voitures Disponibles</h3>
                <div class="number"><?php echo $cars_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Réservations Total</h3>
                <div class="number"><?php echo $reservations_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Réservations En Attente</h3>
                <div class="number"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Réservations Confirmées</h3>
                <div class="number"><?php echo $confirmed_count; ?></div>
            </div>
        </div>

        <div class="actions">
            <a href="cars.php" class="btn">Gérer les Voitures</a>
            <a href="reservations.php" class="btn">Voir les Réservations</a>
            <a href="add_car.php" class="btn">Ajouter une Voiture</a>
        </div>

        <!-- Dernières réservations -->
        <div class="recent-reservations">
            <h2>Dernières Réservations</h2>
            <?php
            try {
                $recent_reservations = $reservation->readAll();
                $count = 0;
                $has_reservations = false;
                
                while ($row = $recent_reservations->fetch(PDO::FETCH_ASSOC)) {
                    if ($count >= 5) break;
                    echo "<div class='reservation-item'>
                            <div>
                                <strong>#{$row['id']}</strong> - {$row['full_name']} 
                                <br><small>{$row['car_name']} - Du {$row['pickup_date']} au {$row['return_date']}</small>
                            </div>
                            <span class='status-{$row['status']}'>{$row['status']}</span>
                          </div>";
                    $count++;
                    $has_reservations = true;
                }
                
                if (!$has_reservations) {
                    echo "<p style='text-align: center; color: #666; padding: 2rem;'>Aucune réservation pour le moment</p>";
                }
            } catch (Exception $e) {
                echo "<p style='text-align: center; color: #dc3545; padding: 2rem;'>Erreur lors du chargement des réservations</p>";
            }
            ?>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="reservations.php" class="btn">Voir toutes les réservations</a>
            </div>
        </div>
    </div>
</body>
</html>