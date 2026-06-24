<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoftwareRequest extends Model
{
    protected $primaryKey = 'software_request_id';

    protected $fillable = [
        'user_id',     
        'software_id', 
        'version',
        'status',
    ];

    // ── Status Constants ──
    const STATUS_PENDING  = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id', 'id');
    }

    // ── Status Helpers ──
    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return (int) $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return (int) $this->status === self::STATUS_REJECTED;
    }

    public function statusLabel(): string
    {
        return match ((int) $this->status) {
            self::STATUS_PENDING  => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default               => 'Unknown',
        };
    }
}
