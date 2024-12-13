<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivileges extends Model
{
    use HasFactory;
    protected $table = "user_privileges";

    public $fillable = [
        'id',
        'user_id',
        'designation_id',
        'privileges',
        'status',
        'created_at',
        'updated_at'
    ];
}
