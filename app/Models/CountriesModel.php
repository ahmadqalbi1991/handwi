<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountriesModel extends Model
{
    use HasFactory;
    protected $table = "countries";
    public $timestamps = false;
    protected $primaryKey = 'countries_id';
    protected $fillable = [
        'countries_nice_name', 'countries_phone_code', 'deleted', 'country_status'
    ];
}
