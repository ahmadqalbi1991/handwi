<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLikes extends Model
{
    use HasFactory;
    public function liked_user() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function post() {
        return $this->belongsTo('App\Models\Post', 'post_id', 'id');
    }
}
