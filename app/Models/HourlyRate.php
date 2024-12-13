<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyRate extends Model
{
    use HasFactory;
    protected $table = "hourly_rate";
    protected $primaryKey = "id";
   
}
