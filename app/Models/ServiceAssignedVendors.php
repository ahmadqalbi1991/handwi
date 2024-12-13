<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAssignedVendors extends Model
{
    use HasFactory;
    public function user() {
        return $this->belongsTo('App\Models\User', 'vendor_user_id', 'id');
    }
    public function order() {
        return $this->belongsTo('App\Models\OrderServiceModel', 'order_id', 'order_id');
    }
}
