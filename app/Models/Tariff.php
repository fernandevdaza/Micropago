<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = ['name','price'];

    protected function transaction()
    {
        return $this->hasMany(Transaction::class,'tariff_id','id');
    }


}
