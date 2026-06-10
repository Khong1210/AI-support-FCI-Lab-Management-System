<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    protected $fillable = [
        'lab_id',
        'requester_name',
        'requester_email',
        'date',
        'start_time',
        'end_time',
        'reason',
        'status',
        'rejection_reason',
    ];

    /**
     * The lab associated with this booking request.
     */
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    /**
     * Helper: human-readable status badge class for Bootstrap.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default    => 'badge-warning',
        };
    }
}
