<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    protected $fillable = [
        'meta_key',
        'meta_value',
    ];
}