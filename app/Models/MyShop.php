<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class MyShop extends Model
{
    use HasFactory;

    protected $table = 'my_shop';
    protected $primaryKey = 'shop_id';
    public $timestamps = false;
    protected $fillable = [
        "user_id",
        "product_id",
        "product_attribute_id",
        "added_time",
        "code",
    ];

    public static function markClaim($ins = [], $claimIds = [], $userId = 0)
    {
        DB::beginTransaction();

        try {
            $ids = '';
            if (!empty($claimIds)) {
                foreach ($claimIds as $key) {
                    DB::table('cash_points')
                        ->where('cash_points_id', $key)
                        ->update(['cash_points_status' => 4]);
                    $ids .= $key . ',';
                }
            }

            $ids = rtrim($ids, ',');
            $points = DB::table('cash_points')
                ->whereRaw("cash_points_id IN ($ids)")
                ->sum('cash_points_total');
            $ins['redemed_points'] = $points;

            DB::table('user_table')
                ->where('user_id', $userId)
                ->update([
                    'used_points' => $points,
                    'user_points' => DB::raw("user_points - $points")
                ]);

            $insertedId = DB::table('claims')->insertGetId($ins);
            DB::commit();

            return $insertedId;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function getShopProducts($user_id = 0, $lang_code = "1", $filter = "", $sort = "")
    {
        return DB::select("select 
                                        campaigns.campaigns_id,
                                        campaigns_title,
                                        campaigns_date,
                                        campaigns_time,
                                        campaigns_date_start,
                                        campaigns_time_start,
                                        campaigns_qty,
                                        campaigns_desc,
                                        campaigns_desc_arabic,
                                        campaigns_image,
                                        campaigns_image2,
                                        campaigns_title_arabic,
                                        campaigns.country_id,
                                        campaigns_status,
                                        product.product_id,
                                        product.product_name,
                                        product.product_name_arabic,
                                        product.product_type,
                                        product.product_desc_full,
                                        product.product_desc_full_arabic,
                                        product.product_desc_short,
                                        product.product_desc_short_arabic,
                                        product.product_sale_from,
                                        product.product_sale_to,
                                        product.product_tag,
                                        product.product_status,
                                        product.product_variation_type,
                                        product.product_taxable,

                                        product.product_unique_iden,
                                        product.product_brand_id,
                                        product_attribute.product_attribute_id,
                                        product_attribute.manage_stock,
                                        product_attribute.allow_back_order,
                                        product_attribute.stock_status,
                                        product_attribute.sold_individually,
                                        product_attribute.weight,
                                        product_attribute.length,
                                        product_attribute.height,
                                        product_attribute.width,
                                        product_attribute.shipping_class,
                                        product_attribute.sale_price,
                                        product_attribute.regular_price,
                                        product.product_image,
                                        product_attribute.image,
                                        product_attribute.shipping_note,
                                        my_shop.shop_id as shop_id,
                                        my_shop.added_time,
                                        my_shop.code,
                                        my_shop.code,
                                        pv_str.product_attr_variation,
                                        pv_str.product_attr_variation_arabic,
                
                                        pv_str.attribute_ids,
                                        pv_str.attribute_values_ids,
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed ,
                                        (select product_attribute.stock_quantity from product_attribute where product_id=product.product_id limit 1) as stock_quantity
                                from my_shop 
                                inner join product on product.product_id = my_shop.product_id   
                                inner join campaigns on campaigns.product_id = my_shop.product_id   
                                left join product_attribute on  product_attribute.product_attribute_id = my_shop.product_attribute_id
                left join (select 
                                product_variations.product_attribute_id, 
                                string_agg(attribute_values::text, ', ') as product_attr_variation, 
                                string_agg(attribute_values_arabic::text, ', ') as product_attr_variation_arabic,
                                string_agg(product_variations.attribute_id::text, ', ') as attribute_ids,
                                string_agg(product_variations.attribute_values_id::text, ', ') as attribute_values_ids
                            from product_variations 
                            left join attribute_values on attribute_values.attribute_values_id = product_variations.attribute_values_id
                            left join attribute on attribute.attribute_id = product_variations.attribute_id 
                            group by product_variations.product_attribute_id) pv_str on pv_str.product_attribute_id = my_shop.product_attribute_id 
                                where product.product_status=1 and my_shop.user_id = '{$user_id}'  {$filter} order by my_shop.shop_id desc");
    }

    public static function getMyEarnings($userId = 0) {
        return DB::table('cash_points')
            ->select('cash_points.*', 'ut.user_first_name as name', 'po.order_placed_date', 'poh.unit_price as purchased_amount', 'c.campaigns_title')
            ->leftJoin('product_order_details as po', 'po.order_block_id', '=', 'cash_points.order_block_id')
            ->leftJoin('product_order_history as poh', 'poh.history_id', '=', 'cash_points.history_id')
            ->join('campaigns as c', 'c.product_id', '=', 'poh.product_id')
            ->join('user_table as ut', 'ut.user_id', '=', 'po.user_id')
            ->where('cash_points_user_id', $userId)
            ->orderBy('cash_points.cash_points_id', 'desc')
            ->get();
    }
}
