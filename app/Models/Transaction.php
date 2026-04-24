<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'tariff_id','vehicle_id','type','amount','status'];

    protected function tariff()
    {
        return $this->belongsTo(Tariff::class, 'tariff_id','id');
    }
    protected function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id','id');
    }
    protected function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

}
