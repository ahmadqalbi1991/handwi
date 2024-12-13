<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ServiceCategories extends Model
{
    //
    protected $table = "service_category";
    protected $primaryKey = "id";

    protected $guarded = [];


    public function children()
    {
        return $this->hasMany('App\Models\ServiceCategories', 'parent_id', 'id');
    }
    
    public function parent()
    {
        return $this->hasOne('App\Models\ServiceCategories', 'id', 'parent_id');
    }

    public function includes()
    {
        return $this->hasMany('App\Models\ServiceInclude', 'service_id', 'id');
    }
    public function s_includes()
    {
        return $this->hasMany('App\Models\ServiceInclude', 'service_id', 'id');
    }
    public function activity() {
        return $this->belongsTo('App\Models\ActivityType', 'activity_id', 'id'); 
    }
    public function getImageAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public function getBannerImageAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public static function sort_item($item = [])
    {
        if (!empty($item)) {
            DB::beginTransaction();
            try {
                $i = 0;
                foreach ($item as $key) {
                    ServiceCategories::where('id', $key)
                        ->update(['sort_order' => $i]);
                    $i++;
                }
                DB::commit();
                return 1;
            } catch (\Exception $e) {
                DB::rollback();
                return 0;
            }
        } else {
            return 0;
        }
    }
    public static function get_service_categories($service_id = 0, $request = '')
    {
        if ($request == 'request') {
            return DB::table("service_category_selected")->where('service_id', '=', $service_id)->get()->toArray();
        }
        return DB::table("service_category_selected")->where('service_id', '=', $service_id)->get()->toArray();
    }

    public static function getCategoriesCondition($data)
    
    {
        return DB::table('service_category')->orderBy('name', 'asc')->get()->toArray();
    }
}