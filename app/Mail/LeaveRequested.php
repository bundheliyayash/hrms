<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave $leave,
        public User $employee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Leave Request Pending — ' . $this->employee->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.leave-requested');
    }
}
