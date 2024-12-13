<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class ServiceOrderRejected extends Model
{
    protected $table = "orders_services_rejected";
    protected $primaryKey = "id";
}
