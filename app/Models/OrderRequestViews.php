<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRequestViews extends Model
{
    use HasFactory;
    protected $table = "order_request_view";
    protected $primaryKey = "id";
   
}
