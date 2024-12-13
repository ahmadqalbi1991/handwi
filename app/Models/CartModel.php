<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CartModel extends Model
{
    use HasFactory;
    protected $table = 'cart';
    protected $primaryKey = 'cart_id';
    public $timestamps = false;

    public static function getTempOrderDetails($condition) {
        return DB::table('temp_product_order_details')
            ->select('*')
            ->where($condition)
            ->first(); // Use first() to get a single record
    }

    public static function deleteUserCart($userId) {
        return DB::table('cart')
            ->where('user_id', $userId)
            ->delete();
    }

    public static function getOrderSpinners($orderBlockId = 0) {
        return DB::table('user_spinner_history')
            ->select('*')
            ->where('order_block_id', $orderBlockId)
            ->get(); // Retrieve all matching records
    }

    public static function createCashPointHistory($data) {
        DB::table('cash_points')->insert($data);
        return DB::getPdo()->lastInsertId(); // Get the last inserted ID
    }

    public static function getShopProductsByCode($code = "") {
        $data = [];
        $c = decryptor($code);

        if ($c) {
            $data = explode("#", $c);
        }

        if ($data) {
            return DB::table('my_shop')
                ->where('user_id', $data[2])
                ->where('product_id', $data[0])
                ->where('product_attribute_id', $data[1])
                ->first(); // Use first() to get a single record
        }

        return null; // Return null if no data found
    }

    public static function createOrderProducts($data) {
        DB::table('product_order_history')->insert($data);
        return DB::getPdo()->lastInsertId(); // Get the last inserted ID
    }

    public static function getProduct($product_id) {
        $t_order_stock_timeout = TOrderStockTimeout;
        return DB::select("select 
                                        campaigns_id,
                                        campaigns_title,
                                        campaigns_date,
                                        campaigns_time,
                                        campaigns_qty,
                                        campaigns_desc,
                                        campaigns_image,
                                        campaigns_title_arabic,
                                        campaigns.country_id,
                                        campaigns_status,
                                        is_spinner,
                                        draw_date,
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
                                        product_attribute.stock_quantity,
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
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                                from campaigns 
                                inner join product on product.product_id = campaigns.product_id	
                                left join product_attribute on  product_attribute.product_id = product.product_id
                                where product.product_id = {$product_id}");
    }

    public static function createOrder($data) {
        DB::table('product_order_details')->insert($data);
        return DB::getPdo()->lastInsertId(); // Get the last inserted ID
    }

    public static function getTempOrderProductDetails($condition) {
        return DB::table('temp_product_order_history')
            ->select('*')
            ->where($condition)
            ->get(); // Use get() to retrieve all records
    }

    public static function createTempOrder($data) {
        DB::table('temp_product_order_details')->insert($data);
        return DB::getPdo()->lastInsertId();
    }

    public static function updateTempOrder($data, $condition) {
        DB::table('temp_product_order_details')->where($condition)->update($data);
        return DB::getPdo()->lastInsertId();
    }

    public static function createBatchTempOrderProducts($data) {
        DB::table('temp_product_order_history')->insert($data);
        return count($data);
    }

    public static function deleteTempOrder($userId) {
        $historyDeleted = DB::table('temp_product_order_history')
            ->where('user_id', $userId)
            ->delete();

        // Delete from temp_product_order_details
        $detailsDeleted = DB::table('temp_product_order_details')
            ->where('user_id', $userId)
            ->delete();

        // Return total number of rows deleted from both tables
        return $historyDeleted + $detailsDeleted;
    }

    public static function getProductCart($where) {
        return self::where($where)->first();
    }

    public static function getVariationBYProductId($arr_id, $lang_code)
    {
        return DB::select("select attribute_name as type,attribute_values from attribute_values  inner join attribute  on attribute.attribute_id = attribute_values.attribute_id 
                                where attribute_values.attribute_values_id= ANY(ARRAY[{$arr_id}]) and attribute_values.attribute_id!=87");
    }

    public static function createBatchOrderTicketNumber($data)
    {
        DB::table('product_order_ticket_number')->insert($data);
        return count($data);
    }

    public static function updateStock($data, $condition) {
        return DB::table('product_attribute')
            ->where($condition)
            ->update($data);
    }

    public static function spinnerHistory($data) {
        DB::table('user_spinner_history')->insert($data);
        return count($data); // Return the number of inserted records
    }

    public static function createBatchDrawSlip($data)
    {
        DB::table('draw_slip')->insert($data);
        return count($data);
    }

    public static function getUserSpinnerByProductProductAttributeId($productId = 0, $productAttributeId = 0, $userId = 0)
    {
        return DB::table('user_spinner_history as up')
            ->select('up.*')
            ->join('product_order_history as poh', 'poh.history_id', '=', 'up.history_id')
            ->where('poh.product_id', $productId)
            ->where('poh.product_attribute_id', $productAttributeId)
            ->where('up.spinner_status', 1)
            ->where('up.spinner_user_id', $userId)
            ->get();
    }

    public static function getSpinnerPrize($language = 1)
    {
        return DB::table('spinner_prize')
            ->select('*')
            ->where('is_deleted', 0)
            ->where('spinner_language_code', $language)
            ->get();
    }


    public static function getUserSpinnerByProductAttributeId($productId = 0, $productAttributeId = 0, $userId = 0) {
        return DB::table('user_spinner_history as up')
            ->select('up.*')
            ->join('product_order_history as poh', 'poh.history_id', '=', 'up.history_id')
            ->where('poh.product_id', $productId)
            ->where('poh.product_attribute_id', $productAttributeId)
            ->where('up.spinner_status', 1)
            ->where('up.spinner_user_id', $userId)
            ->get();
    }

    public static function getVat() {
        return DB::table('vat_percentage')
            ->select('vat_perc')
            ->where('status', 1)
            ->first();

    }

    public static function getCartProductsCheckout($user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
        return DB::select("select 
                                cart.cart_id,	
                                cart.quantity as cart_quantity,	
                                cart.is_donate as is_donate, 
                                campaigns_id,
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
                                is_spinner,
                                draw_date,
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
                                product_attribute.stock_quantity,
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
                                favourate.id as favourate_id,
                                favourate.favourate_added_time,
                                pv_str.product_attr_variation,
                                pv_str.product_attr_variation_arabic,
                                pv_str.attribute_ids,
                                pv_str.attribute_values_ids,
                                (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                        from cart 
                        inner join product on product.product_id = cart.product_id	and product.product_status = 1
                        inner join campaigns on campaigns.product_id = cart.product_id	                                   
                        left join product_attribute on  product_attribute.product_attribute_id = cart.product_attribute_id	
                        left join (select 
                                        product_variations.product_attribute_id, 
                                        string_agg(attribute_values::text, ', ') as product_attr_variation, 
                                        string_agg(attribute_values_arabic::text, ', ') as product_attr_variation_arabic,
                                        string_agg(product_variations.attribute_id::text, ', ') as attribute_ids,
                                        string_agg(product_variations.attribute_values_id::text, ', ') as attribute_values_ids
                                    from product_variations 
                                    left join attribute_values on attribute_values.attribute_values_id = product_variations.attribute_values_id
                                    left join attribute on attribute.attribute_id = product_variations.attribute_id 
                                    group by product_variations.product_attribute_id) pv_str on pv_str.product_attribute_id = product_attribute.product_attribute_id
                        left join favourate on favourate.product_id = product.product_id 
                                    and favourate.user_id = '{$user_id}' where 1=1    {$filter}");
    }

    public static function getCartProducts($user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
        return DB::select("select 
                                            cart.cart_id,	
                                            cart.share_redeem_code,
                                            cart.quantity as cart_quantity,	
                                            cart.is_donate as is_donate, 
                                            campaigns_id,
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
                                            campaigns.campaigns_draw_date,
                                            campaigns_status,
                                            is_spinner,
                                            draw_date,
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
                                            product_attribute.stock_quantity,
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
                                            favourate.id as favourate_id,
                                            favourate.favourate_added_time,
                                            pv_str.product_attr_variation,
                                            pv_str.product_attr_variation_arabic,
                                            pv_str.attribute_ids,
                                            pv_str.attribute_values_ids,
                                            (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                            extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                            (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                            (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                                    from cart 
                                    inner join product on product.product_id = cart.product_id	and product.product_status = 1
                                    inner join campaigns on campaigns.product_id = cart.product_id	                                   
                                    left join product_attribute on  product_attribute.product_attribute_id = cart.product_attribute_id	
                                    left join (select 
                                                    product_variations.product_attribute_id, 
                                                    string_agg(attribute_values::text, ', ') as product_attr_variation, 
                                                    string_agg(attribute_values_arabic::text, ', ') as product_attr_variation_arabic,
                                                    string_agg(product_variations.attribute_id::text, ', ') as attribute_ids,
                                                    string_agg(product_variations.attribute_values_id::text, ', ') as attribute_values_ids
                                                from product_variations 
                                                left join attribute_values on attribute_values.attribute_values_id = product_variations.attribute_values_id
                                                left join attribute on attribute.attribute_id = product_variations.attribute_id 
                                                group by product_variations.product_attribute_id) pv_str on pv_str.product_attribute_id = product_attribute.product_attribute_id
                                    left join favourate on favourate.product_id = product.product_id 
                                                and favourate.user_id = '{$user_id}' where 1=1 and product_attribute.stock_quantity!=0   {$filter}");
    }

    public static function getCountryVat($country_id)
    {
        return DB::table('countries')
            ->select('tax')
            ->where('countries_id', $country_id)
            ->first();
    }
}
