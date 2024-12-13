<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class IndustryTypes extends Model
{
    //
    protected $table = "industry_types";
    protected $primaryKey = "id";

    public $fillable = ['name','created_at','created_uid','updated_at','updated_uid','active','deleted'];
    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                    $i=0;
                    foreach( $item as $key ){
                        IndustryTypes::where('id', $key)
                            ->update(['sort_order' => $i]);
                        $i++;
                    }
                    DB::commit();
                return 1;
            } catch (\Exception $e) {
                DB::rollback();
                return 0;
            }
        }else{
            return 0;
        }
    }
}
