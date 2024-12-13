<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeTypes extends Model
{
    //
    protected $table = "attribute_type";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $guarded = []; 

    
}
