<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ProductMasterModel extends Model
{
    //
    protected $table = "product_master";
    protected $primaryKey = "id";

    protected $guarded = [];

}
