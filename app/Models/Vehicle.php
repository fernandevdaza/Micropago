<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TransportLine;

class Vehicle extends Model
{
    protected $fillable = ['driver_id', 'internal_number', 'license_plate', 'transport_line_id'];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function transportLine()
    {
        return $this->belongsTo(TransportLine::class, 'transport_line_id', 'id');
    }
}
