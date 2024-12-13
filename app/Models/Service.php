<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Service extends Model
{
    //
    protected $table = "service";
    protected $primaryKey = "id";

    protected $guarded = [];

    public function getImageAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
    public static function getAllServices($params = [])
    {
        $obj = Self::whereIn('active', [0, 1])->where('deleted', 0);
        if (isset($params['user_id']) && $params['user_id'] != "") {
            $obj =  $obj->where('service_user_id', $params['user_id']);
        }
        return $obj->paginate(10);
    }
    //    public function service_category(){
    //        $this->belongsTo(ServiceCategories::class);
    //    }
    public function user()
    {
        return $this->belongsTo(User::class, 'service_user_id');
    }

    public function building_type()
    {
        return $this->hasOne('App\Models\BuildingTypes', 'id', 'building_type_id');
    }
    public static function sort_item($item = [])
    {
        if (!empty($item)) {
            DB::beginTransaction();
            //try {
                $i = 0;
                foreach ($item as $key) {
                    Service::where('id', $key)
                        ->update(['sort_order' => $i]);
                    $i++;
                }
                DB::commit();
                return 1;
            // } catch (\Exception $e) {
            //     DB::rollback();
            //     return 0;
            // }
        } else {
            return 0;
        }
    }
}