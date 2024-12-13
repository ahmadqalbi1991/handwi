<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;
    protected $table = "tesimonial";
    public $timestamps = false;
    public function getUserImageAttribute($value){
      if($value)
      {
        return url(config('global.upload_path').config('global.user_image_upload_dir').$value);
      }
      return "";
    }

}
