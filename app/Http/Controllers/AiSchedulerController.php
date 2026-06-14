<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AiSchedulerController extends Controller
{
    /**
     * Display the AI scheduler interface with schedule, software, and equipment data.
     */
    public function index(): View
{
    // Fetch raw table records without joins to avoid column name conflicts
    $schedules = DB::table('schedules')->get();
    $software = DB::table('software')->get();
    $laboratories = DB::table('laboratories')->get();
    $courses = DB::table('courses')->get();
        $lecturers = DB::table('users')->where('user_role', 5)->get();

    $apiKey = env('GOOGLE_AI_KEY');

        return view('ai-scheduler', compact('schedules', 'software', 'laboratories', 'courses', 'lecturers', 'apiKey'));
}
}
