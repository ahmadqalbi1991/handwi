<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class TempServiceModel extends Model
{
    protected $table = "temp_service_orders";
    protected $primaryKey = "id";
    public $timestamps=false;
  
}
