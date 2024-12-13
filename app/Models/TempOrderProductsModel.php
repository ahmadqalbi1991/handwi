<?php

namespace App\Models;
use DB;

use Illuminate\Database\Eloquent\Model;

class TempOrderProductsModel extends Model
{
    //
    protected $table = "temp_order_products";
    protected $primaryKey = "id";

    public $timestamps=false;

    
}
