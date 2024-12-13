<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SettingsModel extends Model
{
    protected $table = "settings";
    protected $primaryKey = "id";
    public $timestamps = false;


    public $fillable = [
        'admin_commission',
    ];
}
