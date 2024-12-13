<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designations extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = "designations";


    public $fillable = [
        'id',
        'user_id',
        'user_type',
        'designation',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
