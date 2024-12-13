<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBanners extends Model
{
    use HasFactory;
    public $appends = ['image_url'];
    public function getImageUrlAttribute(){
        return url(config('global.upload_path').config('global.banner_image_upload_dir').$this->image_name);
    }

    public function getCreatedAtAttribute($value){
        return date('Y-m-d H:i:s',strtotime($value));
    }
    public function getUpdatedAtAttribute($value){
        return date('Y-m-d H:i:s',strtotime($value));
    }
}
