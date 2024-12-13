<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = "notifications";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $fillable = ['title','description','image','created_datetime'];
    public $path            = "/uploads/notifications/";

    // public function getImageAttribute($value)
    // {
    //     if($value)
    //     {
    //         return url($this->path.$value);
    //     }
    //     else
    //     {
    //         return url('public/uploads/user/no_images.png');
    //     }
    // }
}