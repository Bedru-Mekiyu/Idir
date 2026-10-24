<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Member $member,
        public string $idirName,
        public array $data = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'የክፍያ ጥያቄዎ ጸድቋል — '.$this->idirName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.claim-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
