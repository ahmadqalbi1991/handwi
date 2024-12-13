<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankdataModel extends Model
{
    use HasFactory;
    protected $table = "bank_details";
    public $timestamps = true;


    public function getUserImageAttribute($value)
    {
        if($value)
        {
            return asset($value);
        }
        else
        {
            return asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg');
        }
    }
    public function country() {
       return $this->belongsTo(CountryModel::class,'country','id');
    }
    public function bank() {
       return $this->belongsTo(BankModel::class,'bank_name','id');
    }
}
