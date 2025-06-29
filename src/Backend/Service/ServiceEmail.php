<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Backend\Model\Notification;
use App\Backend\Model\QueueJobs;
use App\Backend\Service\Interface\EmailServiceInterface;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\ModeleNonTrouveException;

class ServiceEmail implements EmailServiceInterface
{
    private Notification $notificationModel;
    private QueueJobs $queueJobsModel;

    public function __construct(Notification $notificationModel, QueueJobs $queueJobsModel)
    {
        $this->notificationModel = $notificationModel;
        $this->queueJobsModel = $queueJobsModel;
    }

    public function envoyer(string $destinataire, string $sujet, string $corpsHtml, array $options = []): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = filter_var(getenv('SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN);
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');
            $mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)getenv('SMTP_PORT');

            $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
            $mail->addAddress($destinataire);

            if (isset($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $mail->addCC($cc);
                }
            }
            if (isset($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }
            if (isset($options['attachments'])) {
                foreach ((array)$options['attachments'] as $attachment) {
                    $mail->addAttachment($attachment);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $sujet;
            $mail->Body = $corpsHtml;
            $mail->AltBody = strip_tags($corpsHtml);
            $mail->CharSet = 'UTF-8';

            return $mail->send();
        } catch (PHPMailerException $e) {
            throw new EmailException("Le service d'email a échoué: {$e->errorMessage()}");
        }
    }

    public function envoyerDepuisTemplate(string $destinataire, string $templateCode, array $variables): bool
    {
        $template = $this->notificationModel->trouverUnParCritere(['code_notification' => $templateCode]);

        if (!$template) {
            throw new ModeleNonTrouveException("Le modèle d'email avec le code '{$templateCode}' n'a pas été trouvé.");
        }

        $sujet = $template['sujet'];
        $corps = $template['corps_notification'];

        foreach ($variables as $key => $value) {
            $placeholder = "{{{$key}}}";
            $sujet = str_replace($placeholder, (string) $value, $sujet);
            $corps = str_replace($placeholder, (string) $value, $corps);
        }

        return $this->envoyer($destinataire, $sujet, $corps);
    }

    public function ajouterEmailALaFile(array $parametres): bool
    {
        $payload = json_encode($parametres);

        $donneesTache = [
            'job_name' => 'SendEmail',
            'payload' => $payload,
            'status' => 'pending',
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        return (bool)$this->queueJobsModel->creer($donneesTache);
    }

    public function envoyerEnMasse(array $destinataires, string $sujet, string $corps): int
    {
        $count = 0;
        foreach ($destinataires as $destinataire) {
            $parametres = [
                'destinataire' => $destinataire,
                'sujet' => $sujet,
                'corpsHtml' => $corps
            ];
            if ($this->ajouterEmailALaFile($parametres)) {
                $count++;
            }
        }
        return $count;
    }

    public function getStatutEnvoi(string $idEmail): ?string
    {
        $tache = $this->queueJobsModel->trouverParIdentifiant($idEmail);
        return $tache['status'] ?? null;
    }
}