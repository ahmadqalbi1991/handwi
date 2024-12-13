<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Cart extends Model
{
    use HasFactory;
    protected $table = "cart";
    protected $guarded = [];
    protected $primaryKey = 'cart_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'product_id',
        'product_attribute_id',
        'order_placed',
        'quantity',
        'cart_created_date',
        'anonimus_id',
        'celebrity_id',
        'message_cake',
        'delivery_type',
        'checkout_status',
        'buy_now',
        'is_donate',
        'share_redeem_code',
        'reference_user_id'
    ];
    public static function get_user_cart($where)
    {
        return Cart::where($where)->orderby("cart_id", "asc")->get();
    }

    public static function get_product_cart($condition) {
        return self::where($condition)->first();
    }

    public static function update_cart($data, $condition)
    {
        return Cart::where($condition)->update($data);
    }
    public static function create_cart($data)
    {
        $cart = Cart::create($data);
        if ($cart) {
            return $cart->id;
        } else {
            return 0;
        }
    }

    public static function delete_cart($where)
    {
        self::where($where)->delete();
    }

    public static function get_cart_products($user_id, $lang_code, $filter) {
        $t_order_stock_timeout = 3600;
        return DB::select("select 
                                cart.cart_id,	
                                cart.quantity as cart_quantity,	
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
                                (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                        from cart 
                        inner join product on product.product_id = cart.product_id	
                        inner join campaigns on campaigns.product_id = cart.product_id	
                        left join product_attribute on  product_attribute.product_id = product.product_id
                        left join favourate on favourate.product_id = product.product_id 
                                    and favourate.user_id = '{$user_id}' where 1=1 {$filter}");
    }
}
