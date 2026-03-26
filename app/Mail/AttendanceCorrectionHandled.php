<?php

namespace App\Mail;

use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceCorrectionHandled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AttendanceCorrection $correction,
        public User $handledBy,
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->correction->status);
        return new Envelope(
            subject: "Your Attendance Correction has been {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attendance-correction-handled');
    }
}
