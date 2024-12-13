<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class AttributeAndValues extends Model
{
    //
    protected $table = "attribute_values_request";
    protected $primaryKey = "id";

    protected $guarded = [];

}
