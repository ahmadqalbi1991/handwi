<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpinnerModel extends Model
{
    use HasFactory;
    protected $table = 'spinner_prize';

    public static function getMyAllSpinners($user_id, $lang_code = "1", $filter = "", $sort = "") {
        return DB::select("SELECT 
                                    campaigns.campaigns_id,  
                                    campaigns.campaigns_title, 
                                    product_order_details.order_placed_date, 
                                    product.product_name,
                                    product.product_id,
                                    product_attribute.product_attribute_id,	 
                                    user_spinner_history.*
                                     from user_spinner_history 
                                    join product_order_history on user_spinner_history.order_block_id=product_order_history.order_block_id
                                    and user_spinner_history.spinner_product_id=product_order_history.product_id  
                                    join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status=0
                                    join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
                                    join product_attribute on product_attribute.product_id = product_order_history.product_id and product_attribute.product_attribute_id=
                                    product_order_history.product_attribute_id
                                    join product on product.product_id = product_order_history.product_id	
                                    where spinner_user_id={$user_id} group by 
                                    campaigns.campaigns_id,  
                                    campaigns.campaigns_title, 
                                    product_order_details.order_placed_date, 
                                    product.product_name,
                                    product.product_id,
                                    product_attribute.product_attribute_id,
                                    user_spinner_history.spinner_his_id,
                                    user_spinner_history.spinner_user_id,
                                    user_spinner_history.created_spinner_date,
                                    user_spinner_history.spinner_redeemdate,
                                    user_spinner_history.prize,
                                    user_spinner_history.spinner_status,
                                    user_spinner_history.order_block_id,
                                    user_spinner_history.prize_redeem,
                                    user_spinner_history.spinner_product_id,
                                    user_spinner_history.campaign_id,
                                    user_spinner_history.history_id order by user_spinner_history.spinner_his_id desc");
    }

    public static function getSpinnerDetails($condition)
    {
        return DB::table('user_spinner_history')
            ->where($condition)
            ->first();
    }

    public static function getAlloted($select, $condition) {
        return DB::table('product_order_history')
            ->select($select)
            ->where($condition)
            ->first();
    }

    public static function updateWhere($table, $where, $data)
    {
        $affectedRows = DB::table($table)
            ->where($where)
            ->update($data);

        return $affectedRows;
    }
}
