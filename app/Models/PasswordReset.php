<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;
    protected $table = 'password_reset';
    protected $primaryKey = 'reset_id';
    public $timestamps = false;
    protected $fillable = [
        'reset_id',
        'user_id',
        'session_start_time',
        'session_end_time',
        'reset_code',
        'reset_request_date'
    ];
}
