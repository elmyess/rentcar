<?php
class Email {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Envoyer un email de confirmation de réservation
    public function sendReservationConfirmation($reservation_id) {
        // Récupérer les détails de la réservation
        $query = "SELECT r.*, c.name as car_name, c.price_per_day 
                  FROM reservations r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            return false;
        }
        
        // Calculer le prix total
        $days = (strtotime($reservation['return_date']) - strtotime($reservation['pickup_date'])) / (60 * 60 * 24);
        $total_price = $days * $reservation['price_per_day'];
        
        // Destinataire
        $to = $reservation['email'];
        $subject = "🎉 Confirmation de Réservation - Elayadi Prestige Car";
        
        // Message HTML
        $message = $this->getEmailTemplate($reservation, $total_price);
        
        // Headers pour email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Elayadi Prestige Car <noreply@elayadiprestigecar.com>" . "\r\n";
        $headers .= "Reply-To: elayadiprestigecar@gmail.com" . "\r\n";
        
        // Envoyer l'email
        return mail($to, $subject, $message, $headers);
    }
    
    // Envoyer une notification à l'admin
    public function sendAdminNotification($reservation_id) {
        // Récupérer les détails de la réservation
        $query = "SELECT r.*, c.name as car_name 
                  FROM reservations r 
                  LEFT JOIN cars c ON r.car_id = c.id 
                  WHERE r.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            return false;
        }
        
        $to = "elayadiprestigecar@gmail.com"; // Votre email
        $subject = "📋 Nouvelle Réservation - #{$reservation_id}";
        
        $message = "
        Nouvelle réservation reçue :
        
        Réservation ID: #{$reservation_id}
        Client: {$reservation['full_name']}
        Email: {$reservation['email']}
        Téléphone: {$reservation['phone']}
        Véhicule: {$reservation['car_name']}
        Période: {$reservation['pickup_date']} - {$reservation['return_date']}
        Lieu: {$reservation['pickup_location']}
        
        Date de création: {$reservation['created_at']}
        ";
        
        $headers = "From: noreply@elayadiprestigecar.com\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    // Template HTML pour l'email
    private function getEmailTemplate($reservation, $total_price) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    color: #333; 
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #ffffff;
                }
                .header { 
                    background: #0A2540; 
                    color: white; 
                    padding: 30px 20px;
                    text-align: center;
                }
                .content { 
                    padding: 30px 20px;
                }
                .details { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border-left: 4px solid #D4AF37;
                }
                .footer { 
                    background: #D4AF37; 
                    color: white; 
                    padding: 20px; 
                    text-align: center;
                }
                .contact-info {
                    background: #e9ecef;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                }
                h1 { margin: 0; font-size: 28px; }
                h2 { margin: 10px 0; color: #D4AF37; }
                h3 { color: #0A2540; margin-top: 0; }
                .highlight { color: #D4AF37; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚗 Elayadi Prestige Car</h1>
                    <h2>Confirmation de Réservation</h2>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$reservation['full_name']}</strong>,</p>
                    <p>Votre réservation a été confirmée avec succès ! Voici le récapitulatif :</p>
                    
                    <div class='details'>
                        <h3>📋 Détails de la Réservation</h3>
                        <p><strong>Véhicule:</strong> {$reservation['car_name']}</p>
                        <p><strong>Période:</strong> Du {$reservation['pickup_date']} au {$reservation['return_date']}</p>
                        <p><strong>Lieu de prise en charge:</strong> {$reservation['pickup_location']}</p>
                        <p><strong>Prix total:</strong> <span class='highlight'>{$total_price} DH</span></p>
                        <p><strong>Mode de paiement:</strong> 💵 <span class='highlight'>Cash à la livraison</span></p>
                    </div>
                    
                    <div class='contact-info'>
                        <h3>📞 Nos Coordonnées</h3>
                        <p><strong>Téléphone:</strong> +212660668681</p>
                        <p><strong>Email:</strong> elayadiprestigecar@gmail.com</p>
                        <p><strong>Adresse:</strong> Tit Mellil</p>
                        <p><strong>Heures d'ouverture:</strong> Lundi - Vendredi: 9h-19h, Samedi: 10h-17h</p>
                    </div>
                    
                    <p><strong>ℹ️ Important:</strong></p>
                    <ul>
                        <li>Présentez votre carte d'identité et permis de conduire à la livraison</li>
                        <li>Le véhicule vous sera remis avec le plein de carburant</li>
                        <li>Paiement en cash lors de la remise du véhicule</li>
                    </ul>
                    
                    <p>Merci de votre confiance et à bientôt !</p>
                </div>
                
                <div class='footer'>
                    <p>© 2024 Elayadi Prestige Car - Location de voitures de prestige</p>
                    <p>Tit Mellil | +212660668681</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>