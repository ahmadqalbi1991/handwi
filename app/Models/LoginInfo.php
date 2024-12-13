<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginInfo extends Model
{
    use HasFactory;
    protected $table = 'login_info';
    protected $fillable = [
        'user_id', 'auth_token'
    ];
}
