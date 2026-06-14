<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected static array $statuses = [
        1 => 'Open',
        2 => 'In Progress',
        3 => 'Resolved',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        // 1. Initialize query builder with eager loaded relationship constraints
        $query = Report::with(['user', 'laboratory']);

        /**
         * 2. Enforce structural data isolation boundaries based on administrative clearance.
         * Roles 1 (Admin), 3 (Lab Staff), and 4 (Committee) can audit global tickets.
         * Role 5 (Lecturer) is strictly bounded to view only their personal reported tickets.
         */
        if (!in_array((int)$user->user_role, [1, 2, 3, 4])) {
            $query->where('user_id', $user->id);
        }

        // 3. Apply conditional runtime data filters requested by the interface matching statuses
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 4. Apply conditional runtime data filters targeting explicit laboratory selections
        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

        // 5. Execute compilation query ordered chronologically by placement dates
        return view('admin.reports.index', [
            'reports' => $query->orderByDesc('reported_date')->get(),
            'statuses' => self::$statuses,
            'laboratories' => Laboratory::orderBy('lab_name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.reports.create', [
            'users' => User::orderBy('username')->get(),
            'laboratories' => Laboratory::orderBy('lab_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'lab_id' => 'required|exists:laboratories,id',
            'issues_type' => 'required|string|max:255',
            'description' => 'required|string',
            'reported_date' => 'required|date',
        ]);

        Report::create([
            'user_id' => $request->input('user_id'),
            'lab_id' => $request->input('lab_id'),
            'issues_type' => $request->input('issues_type'),
            'description' => $request->input('description'),
            'reported_date' => $request->input('reported_date'),
            'status' => 1,
        ]);

        return redirect('/reports')->with('status', 'Problem report submitted successfully.');
    }

    public function markInProgress(Report $report)
    {
        $report->update(['status' => 2]);

        return redirect('/reports')->with('status', 'Report marked as in progress.');
    }

    public function resolve(Report $report)
    {
        $report->update(['status' => 3]);

        return redirect('/reports')->with('status', 'Report marked as resolved.');
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return redirect('/reports')->with('status', 'Report deleted.');
    }
}
