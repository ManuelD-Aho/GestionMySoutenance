<?php
namespace App\Backend\Service\Email;

interface ServiceEmailInterface
{
    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool;
}