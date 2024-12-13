<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    use HasFactory;
    protected $guarded = []; 
    public function country()
    {
        return $this->belongsTo('App\Models\CountryModel', 'country_id', 'id');
    }
    public function state()
    {
        return $this->belongsTo('App\Models\States', 'state_id', 'id');
    }
}
