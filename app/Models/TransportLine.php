<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportLine extends Model
{
    protected $fillable = ['name','description'];

    protected function vehicle(){
        return $this->hasMany(Vehicle::class,'transport_line_id','id');
    }
}
