<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;
    protected $table = "area";

    public function country()
    {
        return $this->belongsTo('App\Models\CountryModel', 'country_id', 'id');
    }
    public function city()
    {
        return $this->belongsTo('App\Models\Cities', 'city_id', 'id');
    }
}
