<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'tracking_token',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'status',
        'scheduled_date',
        'scheduled_time_start',
        'scheduled_time_end',
        'eta',
        'distance_to_delivery',
        'courier_id',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_notes',
        'signature_image',
        'payment_method',
        'payment_status',
        'order_value',
        'priority',
        'special_instructions',
    ];

    protected $casts = [
        'delivery_lat' => 'decimal:7',
        'delivery_lng' => 'decimal:7',
        'scheduled_date' => 'date',
        'eta' => 'datetime',
        'distance_to_delivery' => 'decimal:2',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'order_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->tracking_token) {
                $order->tracking_token = bin2hex(random_bytes(32));
            }
        });
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function trackingData()
    {
        return $this->hasMany(TrackingData::class);
    }

    public function deliveryEvents()
    {
        return $this->hasMany(DeliveryEvent::class);
    }

    public function reschedules()
    {
        return $this->hasMany(Reschedule::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeAssignedTo($query, $courierId)
    {
        return $query->where('courier_id', $courierId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['picked_up', 'in_transit', 'nearby']);
    }

    // Helper Methods
    public function sendTrackingEmail()
    {
        if ($this->customer_email) {
            \Illuminate\Support\Facades\Mail::to($this->customer_email)
                ->send(new \App\Mail\OrderTrackingMail($this));
        }
    }

    public function getTrackingUrlAttribute()
    {
        return route('tracking.show', ['token' => $this->tracking_token]);
    }
}
