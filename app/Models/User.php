<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use DB;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'deleted',
        'previllege'
    ];
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];


    public static function update_password($id,$password){
        return DB::table("users")->where("id",'=',$id)->update(['password' =>bcrypt($password)]);
    }

    public function posts() {
        return $this->hasMany('App\Models\Post', 'user_id', 'id');
    }
    public function followed(){
      return $this->hasMany('App\Models\UserFollow','user_id', 'id');
    }
    public function follower(){
      return $this->hasMany('App\Models\UserFollow','follow_user_id', 'id');
    }
    public function vendordata() {
       return $this->hasOne(VendorDetailsModel::class,'user_id');
    }
    public function activity() {
        return $this->hasOne(ActivityType::class,'id','activity_id');
    }
    public function country() {
       return $this->belongsTo(CountryModel::class,'country_id');
    }
    public function city() {
       return $this->belongsTo(Cities::class,'city_id');
    }
    public function state() {
       return $this->belongsTo(States::class,'state_id');
    }
    public function bank_details() {
       return $this->hasOne(BankdataModel::class,'user_id');
    }
    public function cart(){
      return $this->hasMany('App\Models\Cart','user_id', 'id');
    }
    public function service(){
        return $this->hasMany(Service::class,'service_user_id','id');
    }
    public static function getAllVendors()
    {
        return User::where('role', '3')->orderBy('users.id', 'desc')->get();
    }
}
