<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    use HasFactory;
    protected $table = "country";
    public $timestamps = false;
    protected $primaryKey = 'country_id';
    protected $fillable = [
        'country_name', 'country_dial_code', 'country_status', 'country_language_code'
    ];
}
