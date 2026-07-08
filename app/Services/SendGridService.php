<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;

class SendGridService
{
    public static function send($to, $subject, $htmlContent, $name = 'User')
    {
        $email = new Mail();
        $email->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $email->setSubject($subject);
        $email->addTo($to, $name);
        $email->addContent("text/html", $htmlContent);

        $sendgrid = new SendGrid(env('SENDGRID_API_KEY'));

        try {
            return $sendgrid->send($email);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}