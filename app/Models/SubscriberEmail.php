<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberEmail extends Model
{
    use HasFactory;
    protected $table = "subscribers_emails";
    public $timestamps = false;
    public function getUserImageAttribute($value){
      if($value)
      {
        return url(config('global.upload_path').config('global.user_image_upload_dir').$value);
      }
      return "";
    }

}
