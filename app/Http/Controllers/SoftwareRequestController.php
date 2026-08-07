<?php

namespace App\Http\Controllers;

use App\Models\Software;
use App\Models\SoftwareRequest;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SoftwareRequestController extends Controller
{
    /**
     * Display a listing of all software requests.
     */
    public function index()
    {
        $requests = SoftwareRequest::with(['user', 'software'])->get();

        $labs = Laboratory::all();

        return view('admin.software-requests.index', compact('requests', 'labs'));
    }

    /**
     * Show the form for creating a new software request.
     */
    public function create()
    {
        $existingSoftware = Software::where('status', 1)->orderBy('software_name')->get();

        return view('admin.software-requests.create', compact('existingSoftware'));
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'software_name' => 'required|string|max:70',
        'version'       => 'nullable|string|max:20',
    ]);

    $packedString = $data['software_name'] . ($data['version'] ? ' @v' . $data['version'] : '');

    SoftwareRequest::create([
        'user_id'     => Auth::id(),
        'software_id' => 0,
        'version'     => substr($packedString, 0, 255),
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
  public function approve(Request $request, string $id)
    {

        $request->validate([
            'lab_id' => 'required|exists:laboratories,id'
        ]);

        $softwareRequest = SoftwareRequest::findOrFail($id);

        if ($softwareRequest->status !== SoftwareRequest::STATUS_PENDING) {
            return redirect()->route('software-requests.index')
                ->with('error', 'This request has already been processed.');
        }

        $rawString = $softwareRequest->version ?: 'Untitled Software';
        $softwareName = $rawString;
        $softwareVersion = 'Latest';

        if (str_contains($rawString, ' @v')) {
            $parts = explode(' @v', $rawString);
            $softwareName = $parts[0];
            $softwareVersion = $parts[1] ?? 'Latest';
        }

        $newSoftware = Software::create([
            'lab_id'        => $request->input('lab_id'),
            'software_name' => $softwareName,
            'version'       => $softwareVersion,
            'expiry_date'   => '2030-12-31', 
            'status'        => 1,    
        ]);

        $softwareRequest->update([
            'software_id' => $newSoftware->id,
            'status'      => SoftwareRequest::STATUS_APPROVED,
        ]);

        return redirect()->route('software-requests.index')
            ->with('success', 'Request approved! "' . $softwareName . '" added to selected Lab inventory.');
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