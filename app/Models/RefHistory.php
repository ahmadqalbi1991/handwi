<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefHistory extends Model
{
    use HasFactory;
    protected $table = "ref_history";

	public function sender() {
        return $this->hasOne(VendorModel::class,'id','sender_id');
    }
    public function accepted_user() {
        return $this->hasOne(VendorModel::class,'id','accepted_user_id');
    }

   
}
