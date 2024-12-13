<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankModel extends Model
{
    use HasFactory;
    protected $table = "bank";
    public $timestamps = false;

    public $fillable = [
        'name',
        'prefix',
        'active',
        'deleted',
        'created_at',
        'updated_at',
        'dial_code',
    ];
    
}
