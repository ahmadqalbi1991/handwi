<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table = 'city';
    protected $primaryKey = 'city_id';
    protected $fillable = [
        'city_name', 'city_country_id', 'city_language_code', 'city_status'
    ];
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo('App\Models\CountryModel', 'city_country_id', 'country_id')->where('country_language_code', 1);
    }
}
