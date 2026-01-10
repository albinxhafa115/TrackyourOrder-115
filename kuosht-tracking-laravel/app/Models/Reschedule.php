<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'original_date',
        'new_date',
        'reason',
        'notes',
        'requested_by',
    ];

    protected $casts = [
        'original_date' => 'date',
        'new_date' => 'date',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(Courier::class, 'requested_by');
    }
}
