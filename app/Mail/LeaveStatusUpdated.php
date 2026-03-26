<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave $leave,
        public User $handledBy,
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->leave->status);
        return new Envelope(
            subject: "Your Leave Request has been {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.leave-status-updated');
    }
}
