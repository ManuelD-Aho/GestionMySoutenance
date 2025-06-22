<?php
namespace App\Backend\Service\Email;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException; // Renommer pour éviter les conflits
use App\Backend\Model\Notification; // Pour récupérer les modèles d'email de la DB
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Exception\EmailException; // Votre exception personnalisée pour les emails
use App\Backend\Exception\ElementNonTrouveException;

class ServiceEmail implements ServiceEmailInterface
{
    private PHPMailer $mailer;
    private Notification $notificationModel; // Initialisation
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService)
    {
        $this->mailer = new PHPMailer(true);
        $this->notificationModel = new Notification($db); // Initialisation du modèle Notification
        $this->supervisionService = $supervisionService;

        // Configuration SMTP (à externaliser dans un fichier de config .env par exemple)
        // $this->mailer->isSMTP();
        // $this->mailer->Host = 'smtp.example.com';
        // $this->mailer->SMTPAuth = true;
        // $this->mailer->Username = 'user@example.com';
        // $this->mailer->Password = 'password';
        // $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $this->mailer->Port = 587;

        // Remettre la configuration de base si vous ne voulez pas de SMTP tout de suite
        // $this->mailer->isMail(); // Utilise la fonction mail() de PHP par défaut
        $this->mailer->setFrom('no-reply@gestionsoutenance.com', 'GestionMySoutenance'); // Expéditeur par défaut
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Envoie un email.
     * @param array $emailData Données de l'email: destinataire_email, sujet, corps_html (optionnel), corps_texte (optionnel).
     * @return bool Vrai si l'email a été envoyé avec succès.
     * @throws EmailException En cas d'échec de l'envoi de l'email.
     */
    public function envoyerEmail(array $emailData): bool
    {
        try {
            $this->mailer->addAddress($emailData['destinataire_email']);
            $this->mailer->Subject = $emailData['sujet'];
            $this->mailer->Body = $emailData['corps_html'] ?? $emailData['corps_texte'];
            $this->mailer->AltBody = $emailData['corps_texte'] ?? strip_tags($emailData['corps_html'] ?? '');

            $success = $this->mailer->send();
            // Réinitialiser les destinataires pour le prochain envoi
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->supervisionService->enregistrerAction(
                'SYSTEM', // Ou l'ID de l'utilisateur déclencheur si disponible
                'EMAIL_ENVOI_SUCCES',
                "Email envoyé à {$emailData['destinataire_email']} (Sujet: {$emailData['sujet']})",
                $emailData['destinataire_email'],
                'Email'
            );

            return $success;
        } catch (PHPMailerException $e) {
            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'EMAIL_ENVOI_ECHEC',
                "Échec envoi email à {$emailData['destinataire_email']}: " . $e->getMessage(),
                $emailData['destinataire_email'],
                'Email'
            );
            throw new EmailException("Erreur lors de l'envoi de l'e-mail : {$e->getMessage()}");
        } catch (\Exception $e) { // Pour d'autres erreurs inattendues
            $this->supervisionService->enregistrerAction(
                'SYSTEM',
                'EMAIL_ENVOI_ECHEC_GENERIC',
                "Erreur inattendue envoi email à {$emailData['destinataire_email']}: " . $e->getMessage(),
                $emailData['destinataire_email'],
                'Email'
            );
            throw new EmailException("Une erreur inattendue est survenue lors de l'envoi de l'e-mail.");
        }
    }

    /**
     * Envoie un email en utilisant un modèle stocké en base de données.
     * @param string $destinataireEmail L'adresse email du destinataire.
     * @param string $modeleCode L'ID du modèle de notification à utiliser (ex: 'RESET_PASSWORD_EMAIL').
     * @param array $variablesModele Tableau associatif des variables à remplacer dans le modèle (ex: ['{lien_reset}' => 'http://...']).
     * @return bool Vrai si l'email a été envoyé avec succès.
     * @throws ElementNonTrouveException Si le modèle d'email n'est pas trouvé.
     * @throws EmailException En cas d'échec de l'envoi de l'email.
     */
    public function envoyerEmailAvecModele(string $destinataireEmail, string $modeleCode, array $variablesModele = []): bool
    {
        $modeleNotification = $this->notificationModel->trouverParIdentifiant($modeleCode);
        if (!$modeleNotification) {
            throw new ElementNonTrouveException("Modèle d'email '{$modeleCode}' non trouvé.");
        }

        $sujet = $modeleNotification['libelle_notification']; // Ou un champ 'sujet_modele' si disponible
        $corpsHtml = $modeleNotification['contenu'] ?? ''; // Assumer 'contenu' contient le HTML ou texte

        // Remplacer les variables dans le contenu du modèle
        foreach ($variablesModele as $placeholder => $value) {
            $corpsHtml = str_replace($placeholder, $value, $corpsHtml);
        }

        $emailData = [
            'destinataire_email' => $destinataireEmail,
            'sujet' => $sujet,
            'corps_html' => $corpsHtml,
            'corps_texte' => strip_tags($corpsHtml) // Version texte brute
        ];

        return $this->envoyerEmail($emailData);
    }
}