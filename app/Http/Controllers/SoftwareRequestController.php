<?php

namespace App\Http\Controllers;

use App\Models\Software;
use App\Models\SoftwareRequest;
use Illuminate\Http\Request;

class SoftwareRequestController extends Controller
{
    /**
     * Display a listing of all software requests.
     */
    public function index()
    {
        $requests = SoftwareRequest::with(['user', 'software'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.software-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new software request.
     */
    public function create()
    {
        $existingSoftware = Software::where('status', 1)->orderBy('software_name')->get();

        return view('admin.software-requests.create', compact('existingSoftware'));
    }

    /**
     * Store a newly created software request.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'software_name' => 'required|string|max:255',
            'version'       => 'nullable|string|max:100',
            'software_id'   => 'nullable|integer|exists:software,id',
        ]);

        SoftwareRequest::create([
            'user_id'     => auth()->id(),
            'software_id' => $data['software_id'] ?? null,
            'version'     => $data['software_name']
                . ($data['version'] ? ' v' . $data['version'] : ''),
            'status'      => SoftwareRequest::STATUS_PENDING,
        ]);

        return redirect()->route('software-requests.index')
            ->with('success', 'Software request submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $softwareRequest = SoftwareRequest::with(['user', 'software'])->findOrFail($id);

        return view('admin.software-requests.show', compact('softwareRequest'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $softwareRequest = SoftwareRequest::findOrFail($id);
        $softwareRequest->delete();

        return redirect()->route('software-requests.index')
            ->with('success', 'Software request deleted.');
    }

    /**
     * ───  APPROVE: Shadow data → real inventory (one-click pipeline)  ───
     */
    public function approve(string $id)
    {
        $softwareRequest = SoftwareRequest::findOrFail($id);

        // 1. Extract software name from the stored version string
        $softwareName = $softwareRequest->version ?: 'Untitled Software';

        // 2. Create a new Software record in the real inventory
        $newSoftware = Software::create([
            'software_name' => $softwareName,
            'version'       => 'Latest',
            'lab_id'        => 1,
            'status'        => 1,
        ]);

        // 3. Bind the request to the newly created software & mark Approved
        $softwareRequest->update([
            'software_id' => $newSoftware->id,
            'status'      => SoftwareRequest::STATUS_APPROVED,
        ]);

        return redirect()->route('software-requests.index')
            ->with('success', 'Request approved! "' . $softwareName . '" has been added to inventory.');
    }

    /**
     * ───  REJECT: Simply set status to Rejected  ───
     */
    public function reject(string $id)
    {
        $softwareRequest = SoftwareRequest::findOrFail($id);
        $softwareRequest->update(['status' => SoftwareRequest::STATUS_REJECTED]);

        return redirect()->route('software-requests.index')
            ->with('success', 'Software request rejected.');
    }
}