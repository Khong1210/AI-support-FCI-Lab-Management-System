<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected static array $statuses = [
        1 => 'Open',
        2 => 'In Progress',
        3 => 'Resolved',
    ];

    public function index(Request $request)
    {
        $query = Report::with(['user', 'laboratory']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

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

        return redirect('/admin/reports')->with('status', 'Problem report submitted successfully.');
    }

    public function markInProgress(Report $report)
    {
        $report->update(['status' => 2]);

        return redirect('/admin/reports')->with('status', 'Report marked as in progress.');
    }

    public function resolve(Report $report)
    {
        $report->update(['status' => 3]);

        return redirect('/admin/reports')->with('status', 'Report marked as resolved.');
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return redirect('/admin/reports')->with('status', 'Report deleted.');
    }
}
