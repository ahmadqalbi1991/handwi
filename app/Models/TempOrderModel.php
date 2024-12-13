<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class TempOrderModel extends Model
{
    protected $table = "temp_orders";
    protected $primaryKey = "id";
    public $timestamps=false;
  
}
