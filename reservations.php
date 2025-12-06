<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include_once '../config/database.php';
include_once '../models/Reservation.php';

$database = new Database();
$db = $database->getConnection();
$reservation = new Reservation($db);

$message = '';
$error = '';

// DEBUG: Afficher les paramètres GET
error_log("DEBUG - GET params: " . print_r($_GET, true));

// Changer le statut d'une réservation
if(isset($_GET['update_status']) && isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $new_status = $_GET['update_status'];
    
    error_log("DEBUG - Updating reservation $reservation_id to status: $new_status");
    
    // Valider le statut
    $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
    if(!in_array($new_status, $allowed_statuses)) {
        $error = "Statut invalide";
    } else {
        $query = "UPDATE reservations SET status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        if($stmt->execute([$new_status, $reservation_id])) {
            if($stmt->rowCount() > 0) {
                $message = "Réservation #$reservation_id marquée comme " . 
                          ($new_status == 'confirmed' ? 'confirmée' : 
                           ($new_status == 'cancelled' ? 'annulée' : 'en attente'));
                
                // Rediriger pour éviter la resoumission
                header("Location: reservations.php?message=" . urlencode($message));
                exit;
            } else {
                $error = "Aucune réservation trouvée avec cet ID";
            }
        } else {
            $error = "Erreur lors de la mise à jour: " . implode(", ", $stmt->errorInfo());
        }
    }
}

// Vérifier s'il y a un message dans l'URL
if(isset($_GET['message'])) {
    $message = $_GET['message'];
}

$stmt = $reservation->readAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gérer les Réservations - Admin</title>
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
            max-width: 1400px;
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
            font-weight: 600;
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
        .reservations-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }
        th {
            background: #0A2540;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status-pending {
            color: #ff9800;
            font-weight: 600;
            background: #fff3e0;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .status-confirmed {
            color: #4caf50;
            font-weight: 600;
            background: #e8f5e8;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .status-cancelled {
            color: #f44336;
            font-weight: 600;
            background: #ffebee;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.1rem;
            font-weight: 600;
        }
        .btn-confirm {
            background: #4caf50;
            color: white;
        }
        .btn-confirm:hover {
            background: #45a049;
            transform: translateY(-1px);
        }
        .btn-cancel {
            background: #f44336;
            color: white;
        }
        .btn-cancel:hover {
            background: #da190b;
            transform: translateY(-1px);
        }
        .btn-pending {
            background: #ff9800;
            color: white;
        }
        .btn-pending:hover {
            background: #e68900;
            transform: translateY(-1px);
        }
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
            font-style: italic;
        }
        .filters {
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            background: #e9ecef;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-btn.active {
            background: #0A2540;
            color: white;
        }
        .filter-btn:hover {
            transform: translateY(-1px);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .created-at {
            font-size: 0.8rem;
            color: #666;
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
        <h1>Gestion des Réservations</h1>
        
        <?php if($message): ?>
            <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="filters">
            <button class="filter-btn active" onclick="filterReservations('all')">Toutes</button>
            <button class="filter-btn" onclick="filterReservations('pending')">En Attente</button>
            <button class="filter-btn" onclick="filterReservations('confirmed')">Confirmées</button>
            <button class="filter-btn" onclick="filterReservations('cancelled')">Annulées</button>
        </div>

        <div class="reservations-table">
            <table id="reservationsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Voiture</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Lieu</th>
                        <th>Statut</th>
                        <th>Créée le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_reservations = false;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        $has_reservations = true;
                        
                        // Formater la date
                        $created_date = date('d/m/Y H:i', strtotime($row['created_at']));
                    ?>
                    <tr class="reservation-row" data-status="<?php echo $row['status']; ?>">
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['car_name']); ?></strong></td>
                        <td><?php echo $row['pickup_date']; ?></td>
                        <td><?php echo $row['return_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                        <td>
                            <span class="status-<?php echo $row['status']; ?>">
                                <?php 
                                $status_text = '';
                                switch($row['status']) {
                                    case 'pending': $status_text = '⏳ En Attente'; break;
                                    case 'confirmed': $status_text = '✅ Confirmée'; break;
                                    case 'cancelled': $status_text = '❌ Annulée'; break;
                                    default: $status_text = $row['status'];
                                }
                                echo $status_text;
                                ?>
                            </span>
                        </td>
                        <td class="created-at"><?php echo $created_date; ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if($row['status'] == 'pending'): ?>
                                    <a href="reservations.php?id=<?php echo $row['id']; ?>&update_status=confirmed" 
                                       class="btn btn-confirm" 
                                       onclick="return confirm('✅ Confirmer la réservation #<?php echo $row['id']; ?> de <?php echo htmlspecialchars($row['full_name']); ?>?')">
                                        ✓ Confirmer
                                    </a>
                                    <a href="reservations.php?id=<?php echo $row['id']; ?>&update_status=cancelled" 
                                       class="btn btn-cancel" 
                                       onclick="return confirm('❌ Annuler la réservation #<?php echo $row['id']; ?> de <?php echo htmlspecialchars($row['full_name']); ?>?')">
                                        ✗ Annuler
                                    </a>
                                <?php elseif($row['status'] == 'confirmed'): ?>
                                    <span style="color: #4caf50; font-weight: 600;">✓ Confirmée</span>
                                    <a href="reservations.php?id=<?php echo $row['id']; ?>&update_status=cancelled" 
                                       class="btn btn-cancel" 
                                       onclick="return confirm('❌ Annuler la réservation #<?php echo $row['id']; ?> de <?php echo htmlspecialchars($row['full_name']); ?>?')">
                                        ✗ Annuler
                                    </a>
                                <?php elseif($row['status'] == 'cancelled'): ?>
                                    <a href="reservations.php?id=<?php echo $row['id']; ?>&update_status=pending" 
                                       class="btn btn-pending"
                                       onclick="return confirm('⏳ Remettre en attente la réservation #<?php echo $row['id']; ?> de <?php echo htmlspecialchars($row['full_name']); ?>?')">
                                        ⏳ En attente
                                    </a>
                                <?php else: ?>
                                    <span style="color: #666; font-size: 0.9rem;">Aucune action</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$has_reservations): ?>
                    <tr>
                        <td colspan="11" class="no-data">
                            📝 Aucune réservation trouvée
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterReservations(status) {
            const rows = document.querySelectorAll('.reservation-row');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Mettre à jour les boutons actifs
            filterBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrer les lignes
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Auto-refresh toutes les 30 secondes pour voir les changements
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>