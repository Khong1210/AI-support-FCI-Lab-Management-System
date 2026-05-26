<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::orderBy('start_date', 'desc')->get();
        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        return view('admin.semesters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Semester::create($request->only(['name', 'start_date', 'end_date']));

        return redirect('/admin/semesters')->with('status', 'Semester created successfully.');
    }

    public function edit(Semester $semester)
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $semester->update($request->only(['name', 'start_date', 'end_date']));

        return redirect('/admin/semesters')->with('status', 'Semester updated successfully.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return redirect('/admin/semesters')->with('status', 'Semester deleted successfully.');
    }
}
