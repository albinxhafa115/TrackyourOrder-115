<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'order_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    // Relationships
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
