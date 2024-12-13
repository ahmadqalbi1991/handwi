<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentLikes extends Model
{
    use HasFactory;
    public function liked_user() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function comment() {
        return $this->belongsTo('App\Models\PostComment', 'comment_id', 'id');
    }
}
