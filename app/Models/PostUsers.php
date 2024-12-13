<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostUsers extends Model
{
    use HasFactory;
    public function post() {
        return $this->belongsTo('Posts', 'post_id', 'id');
    }
    public function user(){
      return $this->hasOne('App\Models\User','id','user_id');
    }
}
