<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodeToRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $code;

    // Constructor receives the verification code
    public function __construct($email,$code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    // Build the email message with a markdown view
    public function build()
    {
        return $this->markdown('emails.code_to_register') // The markdown view
                    ->subject('Code To Register');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
