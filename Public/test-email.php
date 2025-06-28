<?php
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Configuration serveur
    $mail->isSMTP();
    $mail->Host       = 'mailhog';  // Dans Docker, utiliser le nom du service
    $mail->SMTPAuth   = false;      // Pas d'authentification avec MailHog
    $mail->Port       = 1025;       // Port SMTP de MailHog

    // Destinataires
    $mail->setFrom('no-reply@gestionsoutenance.dev', 'GestionMySoutenance Test');
    $mail->addAddress('etudiant@test.com', 'Étudiant Test');

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = '🧪 Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body    = '
    <h1>Test Email depuis GestionMySoutenance</h1>
    <p>Ceci est un email de test envoyé le <strong>' . date('Y-m-d H:i:s') . '</strong></p>
    <p>Si vous recevez cet email dans MailHog, la configuration fonctionne parfaitement ! 🎉</p>
    <hr>
    <p><small>Envoyé depuis : ManuelD-Aho</small></p>
    ';

    $mail->send();
    echo '✅ Email envoyé avec succès !<br>';
    echo '📬 Vérifiez MailHog : <a href="http://localhost:8025" target="_blank">http://localhost:8025</a>';
} catch (Exception $e) {
    echo "❌ Erreur d'envoi : {$mail->ErrorInfo}";
}
?>