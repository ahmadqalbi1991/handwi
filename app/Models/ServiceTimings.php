<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTimings extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id','has_24_hour','service_id','day','time_from','time_to','created_at','updated_at'];
}
