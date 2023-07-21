<?php

require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAILER_USERNAME'];
        $this->mailer->Password = $_ENV['MAILER_PASSWORD'];
        $this->mailer->SMTPAutoTLS = false;
        $this->mailer->Port = 587;
        $this->mailer->setFrom($_ENV['MAILER_USERNAME'], 'Search Console');
        $this->mailer->isHTML(true);
        // $this->mailer->SMTPSecure = 'tls';
    }

    public function sendEmail($subject, $body)
    {
        try {
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($_ENV['RECIPIENT_MAIL']);
            $this->mailer->Subject = $subject . ' (' . date('d.m.Y') . ')';
            $this->mailer->Body = $body;
            $this->mailer->ContentType = 'text/html; charset=UTF-8';
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            echo $e;
            return false;
        }
    }
}
