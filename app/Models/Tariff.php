<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = ['name','price'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class,'tariff_id','id');
    }


}
