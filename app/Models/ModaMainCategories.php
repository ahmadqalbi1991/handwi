<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModaMainCategories extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function sub_categories()
    {
        return $this->hasMany(ModaSubCategories::class, 'main_category', 'id');
    }
}
