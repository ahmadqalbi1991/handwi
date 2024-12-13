<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModaSubCategories extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function getImageAttribute($value)
    {
        return asset($value);
    }
    public static function sort_item($item=[]){
        if( !empty($item) ){
            DB::beginTransaction();
            try {
                    $i=0;
                    foreach( $item as $key ){
                        ModaSubCategories::where('id', $key)
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
    public function items()
    {
        return $this->hasMany(MyModaItems::class, 'moda_sub_category', 'id');
    }
}

