<?php

namespace App\Mail;

use App\Models\User;
use App\Models\EmployeeDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmployee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $employee,
        public EmployeeDetail $detail,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' — Your Account is Ready',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome-employee');
    }
}
