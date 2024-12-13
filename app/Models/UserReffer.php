<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReffer extends Model
{
    use HasFactory;
    protected $table = 'user_referrer';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'refered_user_id', 'points_earned', 'user_referrer_id'
    ];

    public static function createUserReferrer($data) {
        $user = self::create($data);
        return $user->id;
    }
}
