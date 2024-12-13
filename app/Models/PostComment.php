<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

    public function taged_users(){
      return $this->hasMany('App\Models\CommentTagedUsers','comment_id','id');
    }
    public function post() {
        return $this->belongsTo('App\Models\Post', 'post_id', 'id');
    }
    public function parent() {
        return $this->belongsTo('App\Models\PostComment', 'parent_id', 'id');
    }
    public function commented_user() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
