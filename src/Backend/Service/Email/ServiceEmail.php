<?php

namespace App\Backend\Service\Email;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Backend\Model\Notification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceEmail implements ServiceEmailInterface
{
    private PHPMailer $mailer;
    private Notification $notificationModel;
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(
        PDO $db,
        Notification $notificationModel,
        ServiceSupervisionAdmin $supervisionService
    ) {
        $this->mailer = new PHPMailer(true);
        $this->notificationModel = $notificationModel;
        $this->supervisionService = $supervisionService;

        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('SMTP_HOST') ?: 'localhost';
        $this->mailer->SMTPAuth = (bool)getenv('SMTP_AUTH') ?: true;
        $this->mailer->Username = getenv('SMTP_USER') ?: '';
        $this->mailer->Password = getenv('SMTP_PASS') ?: '';
        $this->mailer->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)getenv('SMTP_PORT') ?: 587;

        $this->mailer->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@gestionsoutenance.com', getenv('MAIL_FROM_NAME') ?: 'GestionMySoutenance');
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function envoyerEmail(array $emailData): bool
    {
        try {
            $this->mailer->addAddress($emailData['destinataire_email']);
            $this->mailer->Subject = $emailData['sujet'];
            $this->mailer->Body = $emailData['corps_html'] ?? $emailData['corps_texte'];
            $this->mailer->AltBody = $emailData['corps_texte'] ?? strip_tags($emailData['corps_html'] ?? '');

            $success = $this->mailer->send();

            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->supervisionService->enregistrerAction(
                'SYSTEM',
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
        } catch (\Exception $e) {
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

    public function envoyerEmailAvecModele(string $destinataireEmail, string $modeleCode, array $variablesModele = []): bool
    {
        $modeleNotification = $this->notificationModel->trouverParIdentifiant($modeleCode);
        if (!$modeleNotification) {
            throw new ElementNonTrouveException("Modèle d'email '{$modeleCode}' non trouvé.");
        }

        $sujet = $modeleNotification['libelle_notification'];
        $corpsHtml = $modeleNotification['contenu'] ?? '';

        foreach ($variablesModele as $placeholder => $value) {
            $corpsHtml = str_replace($placeholder, (string)$value, $corpsHtml);
        }

        $emailData = [
            'destinataire_email' => $destinataireEmail,
            'sujet' => $sujet,
            'corps_html' => $corpsHtml,
            'corps_texte' => strip_tags($corpsHtml)
        ];

        return $this->envoyerEmail($emailData);
    }
}