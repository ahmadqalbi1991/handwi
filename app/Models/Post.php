<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    public function post_users() {
        return $this->hasMany('App\Models\PostUsers', 'post_id', 'id');
    }
    public function getFileAttribute($value){
      return get_uploaded_image_url($value,'post_image_upload_dir');
    }
    public function comments() {
        return $this->hasMany('App\Models\PostComment', 'post_id', 'id');
    }
    public function likes() {
        return $this->hasMany('App\Models\PostLikes', 'post_id', 'id');
    }
    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function getExtraFileNamesAttribute($value){
      $files = [];
      $items = explode(",",$value);
      if(!empty($items)){
        foreach($items as $item){
          if($item != ''){
            $files[] = get_uploaded_image_url($item,'post_image_upload_dir');
          }
        }
      }
      return $files;
    }
}
