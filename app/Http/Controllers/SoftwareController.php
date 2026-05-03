<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Software;
use Illuminate\Http\Request;

class SoftwareController extends Controller
{
    protected static array $softwareStatuses = [
        1 => 'Active',
        2 => 'Expired',
        3 => 'Disabled',
    ];

    public function index(Request $request)
    {
        $query = Software::with('laboratory');

        if ($search = $request->input('search')) {
            $query->where('software_name', 'like', "%{$search}%")
                ->orWhere('version', 'like', "%{$search}%");
        }

        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

        return view('admin.software.index', [
            'software' => $query->orderBy('id')->get(),
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$softwareStatuses,
        ]);
    }

    public function create()
    {
        return view('admin.software.create', [
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$softwareStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'software_name' => 'required|string|max:255',
            'version' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'status' => 'required|integer|in:1,2,3',
        ]);

        Software::create($request->only(['lab_id', 'software_name', 'version', 'expiry_date', 'status']));

        return redirect('/admin/software')->with('status', 'Software created successfully.');
    }

    public function edit(Software $software)
    {
        return view('admin.software.edit', [
            'software' => $software,
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$softwareStatuses,
        ]);
    }

    public function update(Request $request, Software $software)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'software_name' => 'required|string|max:255',
            'version' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'status' => 'required|integer|in:1,2,3',
        ]);

        $software->update($request->only(['lab_id', 'software_name', 'version', 'expiry_date', 'status']));

        return redirect('/admin/software')->with('status', 'Software updated successfully.');
    }

    public function destroy(Software $software)
    {
        $software->delete();

        return redirect('/admin/software')->with('status', 'Software deleted successfully.');
    }
}
