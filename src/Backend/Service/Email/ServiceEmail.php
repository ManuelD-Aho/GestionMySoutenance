<?php
namespace App\Backend\Service\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\EmailException;

class ServiceEmail implements ServiceEmailInterface
{
    private PHPMailer $mailer;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(ServiceSupervisionAdminInterface $supervisionService)
    {
        $this->mailer = new PHPMailer(true);
        $this->supervisionService = $supervisionService;
        $this->configureMailer();
    }

    private function configureMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? 'user';
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? 'pass';
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@gestionsoutenance.com', $_ENV['MAIL_FROM_NAME'] ?? 'GestionMySoutenance');
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody ?? strip_tags($htmlBody);

            $success = $this->mailer->send();
            $this->mailer->clearAddresses();

            $this->supervisionService->enregistrerAction('SYSTEM', 'EMAIL_ENVOI_SUCCES', "Email envoyé à {$to} (Sujet: {$subject})", $to, 'Email');
            return $success;
        } catch (PHPMailerException $e) {
            $this->supervisionService->enregistrerAction('SYSTEM', 'EMAIL_ENVOI_ECHEC', "Échec envoi email à {$to}: " . $this->mailer->ErrorInfo, $to, 'Email');
            throw new EmailException("Erreur lors de l'envoi de l'e-mail : " . $this->mailer->ErrorInfo);
        }
    }
}