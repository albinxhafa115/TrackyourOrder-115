<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Courier extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'phone',
        'device_id',
        'status',
        'current_lat',
        'current_lng',
        'last_location_update',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'current_lat' => 'decimal:7',
        'current_lng' => 'decimal:7',
        'last_location_update' => 'datetime',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
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
        return $this->hasMany(Reschedule::class, 'requested_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors & Mutators
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }
}
