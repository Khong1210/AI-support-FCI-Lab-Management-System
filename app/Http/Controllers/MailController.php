<?php

namespace App\Http\Controllers;

use App\Models\SystemMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MailController extends Controller
{
    /**
     * Show the user's system announcements and notifications.
     * Accessible at GET /management/mail
     */
    public function index(Request $request)
    {
       // 1. Capture the authenticated user ID from the active session context
        $userId = Auth::id();

        // Fetch all mails for this user
        $mails = SystemMail::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        // Automatically mark all unread mails as read upon viewing the index page
        SystemMail::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.mail.index', compact('mails'));
    }
}
