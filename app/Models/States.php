<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    use HasFactory;
    protected $guarded = []; 
    public function country()
    {
        return $this->belongsTo('App\Models\CountryModel','country_id','id');
    }
}
