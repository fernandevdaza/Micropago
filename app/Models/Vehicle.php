<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['driver_id','internal_number','license_plate','transport_line_id'];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'driver_id', 'id');
    }
    protected function transportLine()
    {
        return $this->belongsTo(\App\Models\TransportLine::class, 'transport_line_id', 'id');
    }
}
