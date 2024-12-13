<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuisine extends Model
{
    use HasFactory;
   	protected $table = "cuisines";
    protected $guarded = []; 
    
    public function product_cuisine() {
        return $this->hasMany(ProductCuisines::class,'cuisine_id','id');
    }

    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                $i=0;
                foreach( $item as $key ){
                    Cuisine::where('id', $key)
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
