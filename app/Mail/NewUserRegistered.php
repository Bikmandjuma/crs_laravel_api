<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $firstname;
    public $lastname;
    public $gender;
    public $email;
    public $birthdate;

    public function __construct($firstname,$lastname,$gender,$email,$birthdate)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->gender = $gender;
        $this->email = $email;
        $this->birthdate = $birthdate;
    }

    public function build()
    {
        return $this->markdown('emails.new-seeker-register');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Seeker register',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
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
