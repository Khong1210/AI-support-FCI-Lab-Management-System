<?php

namespace App\Mail;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * BookingRejectedMail
 *
 * -----------------------------------------------------------------------
 * DEMO / PRESENTATION STRATEGY
 * -----------------------------------------------------------------------
 * This Mailable is fully wired up with the correct Laravel Mail structure.
 * Because the SMTP server is not yet configured, the `send()` call in the
 * controller is replaced with a Log::info() simulation that writes the
 * complete email body to storage/logs/laravel.log.
 *
 * To activate REAL email delivery once SMTP is ready:
 *   1. Set MAIL_* variables in your .env file.
 *   2. In BookingRequestController::reject(), replace the Log::info() block
 *      with: Mail::to($bookingRequest->requester_email)->send(new BookingRejectedMail($bookingRequest));
 *   3. Done — no other changes needed. The Mailable is already production-ready.
 * -----------------------------------------------------------------------
 */
class BookingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly BookingRequest $bookingRequest
    ) {}

    /**
     * Get the message envelope (subject line, from address, etc.)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[FCI Lab] Your Lab Booking Request Has Been Rejected',
        );
    }

    /**
     * Get the message content definition.
     * Points to the Blade view resources/views/mail/booking-rejected.blade.php
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-rejected',
            with: [
                'bookingRequest' => $this->bookingRequest,
            ],
        );
    }

    /**
     * Simulate sending — writes the email content to the Laravel log.
     * This method is called by the controller instead of Mail::send() for now.
     *
     * To switch to real mail: remove this method and use Mail::to()->send() in the controller.
     */
    public function simulateSend(): void
    {
        $br = $this->bookingRequest;

        Log::info('📧 [MOCK EMAIL — BookingRejectedMail] Email would be sent to: ' . $br->requester_email, [
            'to'          => $br->requester_email,
            'subject'     => '[FCI Lab] Your Lab Booking Request Has Been Rejected',
            'requester'   => $br->requester_name,
            'lab'         => $br->laboratory->lab_name ?? "Lab #{$br->lab_id}",
            'date'        => $br->date,
            'time'        => substr($br->start_time, 0, 5) . ' – ' . substr($br->end_time, 0, 5),
            'reason'      => $br->reason,
            'rejection_reason' => $br->rejection_reason ?? 'Not specified.',
            'body_preview' => "Dear {$br->requester_name}, we regret to inform you that your booking request for "
                . ($br->laboratory->lab_name ?? "Lab #{$br->lab_id}")
                . " on {$br->date} ({$br->start_time}–{$br->end_time}) has been rejected. "
                . "Reason: " . ($br->rejection_reason ?? 'Not specified.')
                . " Please contact the lab administrator if you have questions.",
        ]);
    }
}
