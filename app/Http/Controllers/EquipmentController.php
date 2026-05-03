<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    protected static array $equipmentStatuses = [
        1 => 'Available',
        2 => 'Under Repair',
        3 => 'Retired',
    ];

    public function index(Request $request)
    {
        $query = Equipment::with('laboratory');

        if ($search = $request->input('search')) {
            $query->where('equipment_name', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        }

        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

        return view('admin.equipment.index', [
            'equipment' => $query->orderBy('id')->get(),
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$equipmentStatuses,
        ]);
    }

    public function create()
    {
        return view('admin.equipment.create', [
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$equipmentStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'equipment_name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:equipment,serial_number',
            'type' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'required|integer|in:1,2,3',
        ]);

        Equipment::create($request->only(['lab_id', 'equipment_name', 'serial_number', 'type', 'purchase_date', 'status']));

        return redirect('/admin/equipment')->with('status', 'Equipment created successfully.');
    }

    public function edit(Equipment $equipment)
    {
        return view('admin.equipment.edit', [
            'equipment' => $equipment,
            'laboratories' => Laboratory::orderBy('id')->get(),
            'statuses' => self::$equipmentStatuses,
        ]);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'equipment_name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:equipment,serial_number,' . $equipment->id,
            'type' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'required|integer|in:1,2,3',
        ]);

        $equipment->update($request->only(['lab_id', 'equipment_name', 'serial_number', 'type', 'purchase_date', 'status']));

        return redirect('/admin/equipment')->with('status', 'Equipment updated successfully.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return redirect('/admin/equipment')->with('status', 'Equipment deleted successfully.');
    }
}
