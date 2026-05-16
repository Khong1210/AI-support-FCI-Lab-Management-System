<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    protected static array $laboratoryStatuses = [
        1 => 'Available',
        2 => 'In Maintenance',
        3 => 'Closed',
    ];

    public function index(Request $request)
    {
        $query = Laboratory::withCount(['equipments', 'softwares']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return view('admin.laboratories.index', [
            'laboratories' => $query->orderBy('id')->get(),
            'statuses' => self::$laboratoryStatuses,
        ]);
    }

    public function create()
    {
        return view('admin.laboratories.create', [
            'statuses' => self::$laboratoryStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lab_name' => 'required|string|max:255',
            'status' => 'required|integer|in:1,2,3',
            'capacity' => 'required|integer|min:1',
        ]);

        Laboratory::create([
            'lab_name' => $request->input('lab_name'),
            'status' => $request->input('status'),
            'capacity' => $request->input('capacity'),
        ]);

        return redirect('/admin/laboratories')->with('status', 'Laboratory created successfully.');
    }

    public function edit(Laboratory $laboratory)
    {
        $laboratory->load(['equipments', 'softwares']);

        return view('admin.laboratories.edit', [
            'laboratory' => $laboratory,
            'statuses' => self::$laboratoryStatuses,
        ]);
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $request->validate([
            'lab_name' => 'required|string|max:255',
            'status' => 'required|integer|in:1,2,3',
            'capacity' => 'required|integer|min:1',
        ]);

        $laboratory->update([
            'lab_name' => $request->input('lab_name'),
            'status' => $request->input('status'),
            'capacity' => $request->input('capacity'),
        ]);

        return redirect('/admin/laboratories')->with('status', 'Laboratory updated successfully.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect('/admin/laboratories')->with('status', 'Laboratory deleted successfully.');
    }
}
