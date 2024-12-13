<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class ServiceOrderMuted extends Model
{
    protected $table = "orders_services_mute";
    protected $primaryKey = "id";
}
