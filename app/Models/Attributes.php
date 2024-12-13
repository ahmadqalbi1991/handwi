<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attributes extends Model
{
    //
    protected $table = "attributes";
    protected $primaryKey = "id";

    protected $guarded = []; 
    
    public function attribute_types()
    {
        return $this->hasOne('App\Models\AttributeTypes','id','attribute_type');
    }
    public function industry_types()
    {
        return $this->hasOne('App\Models\IndustryTypes','id','industry_type');
    }
}
