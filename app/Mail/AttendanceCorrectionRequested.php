<?php

namespace App\Mail;

use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceCorrectionRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AttendanceCorrection $correction,
        public User $employee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Attendance Correction Request — ' . $this->employee->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attendance-correction-requested');
    }
}
