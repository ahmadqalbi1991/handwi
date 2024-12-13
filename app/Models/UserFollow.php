<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    use HasFactory;

    public function followed(){
      return $this->belongsTo('App\Models\User', 'follow_user_id', 'id');
    }
    public function follower(){
      return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
