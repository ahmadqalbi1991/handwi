<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class TempServiceModelItems extends Model
{
    protected $table = "temp_service_order_items";
    protected $primaryKey = "id";
    public $timestamps=false;
  
}
