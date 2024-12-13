<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemp extends Model
{
    use HasFactory;

    protected $table = 'user_table_temp';
    public $timestamps = false;
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_first_name',
        'user_middle_name',
        'user_last_name',
        'user_gender',
        'user_email_id',
        'user_password',
        'user_country_id',
        'phone_number',
        'dial_code',
        'user_type',
        'referal_code',
        'invited_user_id',
        'user_status',
        'user_created_by',
        'user_created_date',
        'user_phone_otp',
        'firebase_user_key',
        'invited_user_id',
        'mazouz_customer',
        'is_social'
    ];

    public static function createUserTemp($data)
    {
        $user = self::create($data);
        return $user->user_id;
    }

    public static function updateUserTemp($data, $user_id)
    {
        return self::where("user_id", $user_id)
            ->update($data);
    }
}
