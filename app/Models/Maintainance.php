<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintainance extends Model
{
    use HasFactory;
    protected $table = "maintainance";
    protected $primaryKey = "id";

    protected $guarded = [];
    public $appends =['processed_file_urls'];
    
    public function getProcessedFileUrlsAttribute(){
        $files = $this->multiple_files;
        $files = json_decode($files);
        $return = [];
        if($files){
            foreach($files as $file){
                $return[] = get_uploaded_image_url($file, 'contracts_image_upload_dir');
            }
        }
        if(empty($files)){
            $return[] = get_uploaded_image_url($this->file, 'contracts_image_upload_dir');
        }
        return $return;
    }
    // public function getCreatedAtAttribute($value){
    //     return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');

    // }

    public function getFileAttribute($value)
    {
        return get_uploaded_image_url($value, 'maintainance_image_upload_dir');
    }

    public function getQoutationFileAttribute($value)
    {
        return get_uploaded_image_url($value, 'contracts_image_upload_dir');
    }

    public function building_type()
    {
        return $this->hasOne('App\Models\BuildingTypes', 'id', 'building_type');
    }
     
    public function building_list()
    {
        return $this->hasOne('App\Models\BuildingTypes', 'id', 'building_type');
    }
    
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}