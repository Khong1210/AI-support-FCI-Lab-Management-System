{{--
    Mail View: Booking Rejected Notification
    ==========================================
    This Blade template renders the email body for the BookingRejectedMail Mailable.

    In DEMO/MOCK mode: The content is captured as a string and logged to storage/logs/laravel.log.
    In PRODUCTION mode: Configure MAIL_* in .env and this view will be rendered into a real HTML email.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #dc2626; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .body { padding: 32px; }
        .detail-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .detail-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .detail-table td:first-child { font-weight: bold; color: #555; width: 38%; }
        .reason-box { background: #fff5f5; border-left: 4px solid #dc2626; padding: 14px 16px; border-radius: 4px; margin: 20px 0; font-size: 14px; color: #7f1d1d; }
        .footer { background: #f9fafb; padding: 20px 32px; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Lab Booking Request — Update</h1>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $bookingRequest->requester_name }}</strong>,</p>
        <p>
            We regret to inform you that your lab booking request has been
            <strong style="color:#dc2626;">rejected</strong> by the administrator.
        </p>

        <table class="detail-table">
            <tr><td>Laboratory:</td><td>{{ $bookingRequest->laboratory->lab_name ?? "Lab #{$bookingRequest->lab_id}" }}</td></tr>
            <tr><td>Date:</td><td>{{ \Carbon\Carbon::parse($bookingRequest->date)->format('l, d F Y') }}</td></tr>
            <tr><td>Time:</td><td>{{ substr($bookingRequest->start_time, 0, 5) }} – {{ substr($bookingRequest->end_time, 0, 5) }}</td></tr>
            <tr><td>Your Reason:</td><td>{{ $bookingRequest->reason }}</td></tr>
        </table>

        @if($bookingRequest->rejection_reason)
        <p><strong>Reason for Rejection:</strong></p>
        <div class="reason-box">{{ $bookingRequest->rejection_reason }}</div>
        @endif

        <p>
            If you believe this is an error or you would like to submit a new request for a different
            time slot, please visit our booking portal or contact the lab administrator directly.
        </p>

        <p>Thank you for your understanding.</p>

        <p>
            Best regards,<br>
            <strong>FCI Lab Management Team</strong>
        </p>
    </div>
    <div class="footer">
        This is an automated notification from the FCI Lab Management System.
        Please do not reply to this email.
    </div>
</div>
</body>
</html>
