<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingTypes extends Model
{
    use HasFactory;
    
    protected $table = "building_type";
    
    protected $primaryKey = "id";
    
    protected $guarded = [];

    protected $fillable = ['name', 'description'];
   
   	protected $casts = [
    	'name' => 'string', 
    	'description' => 'string', 
    ];

}
