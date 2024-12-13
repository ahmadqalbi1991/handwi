<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class OrderModel extends Model
{
    protected $table = "product_order_details";
    protected $primaryKey = "product_order_id";

    public function ticketNumber() {
        return $this->hasOne(TicketModel::class, 'order_block_id', 'order_block_id');
    }

    public static function get_spin_history($order_block_id, $history_id)
    {
        return DB::select('*')
            ->table('user_spinner_history')
            ->where('order_block_id', $order_block_id)
            ->where('history_id', $history_id)
            ->get();
    }

    public static function getOrderStatusHistory($orderBlockId)
    {
        return DB::table('product_tracking_status')
            ->select('*')
            ->where('order_block_id', $orderBlockId)
            ->orderBy('product_tracking_status_id', 'DESC')
            ->limit(1)
            ->first();
    }

    public static function getDonationDetails($orderBlockId)
    {
        return DB::table('product_order_history')
            ->select('is_donate')
            ->where('order_block_id', $orderBlockId)
            ->get();
    }

    public static function getVariationByProductId($arrId, $lang)
    {
        return DB::table('attribute_values')
            ->select('attribute_values_id', 'attribute_values')
            ->where('attribute_values_id', $arrId)
            ->get();
    }


    public static function getOrdersWithProducts($user_id, $limit) {
        return DB::select("select 
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

                                    product_order_history.unit_price,
                                    product_order_history.unit_shipping_charge,
                                    product_order_history.product_total_shipping_charge,	
                                    product_order_history.product_total_tax,	
                                    product_order_history.product_sub_price,
                                    product_order_history.product_total,
                                    product_order_history.order_block_id,

                                    (select sum (product_order_history.purchase_qty) from product_order_history where order_block_id=product_order_details.order_block_id) as purchase_qty,

                                    product_order_history.is_donate,
                                    product_order_history.is_spinner,

                                    product_order_details.sub_total,
                                    product_order_details.total_price,
                                    product_order_details.vat_price,
                                    product_order_details.shipping_charge,
                                    product_order_details.discount_price,
                                    product_order_details.shipping_charge,
                                    product_order_details.order_placed_date,
                                    product_order_details.actual_amount_paid,
                                    product_order_details.redeemed_amount,
                                    product_order_details.buy_mode,
                                    product_order_details.donation,
                                    product_order_details.used_points,
                                    pv_str.product_attr_variation,
                                    pv_str.product_attr_variation_arabic,

                                    pv_str.attribute_ids,
                                    pv_str.attribute_values_ids	

                                from (
                                        select 
                                            max(order_block_id) as order_block_id, 
                                            max(history_id) as history_id
                                        from product_order_history where user_id = {$user_id}
                                        group by order_block_id order by history_id asc
                                    ) orders 
                                left join product_order_history on product_order_history.history_id = orders.history_id 
                                left join product_order_details on product_order_details.order_block_id = orders.order_block_id
                                left join product on product.product_id = product_order_history.product_id	
                                left join campaigns on campaigns.product_id = product_order_history.product_id	
                                left join product_attribute on product_attribute.product_attribute_id = product_order_history.product_attribute_id	
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
                                order by product_order_details.order_block_id desc{$limit}");
    }

    public static function getOrderStatus($orderBlockId)
    {
        return DB::table('product_order_status_history')
            ->select('*')
            ->where('order_block_id', $orderBlockId)
            ->orderBy('order_status', 'desc')
            ->first();
    }

    public function products()
    {
        return $this->hasMany(OrderProductsModel::class, 'order_id', 'order_id');

    }

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function vendor()
    {
        return $this->hasOne(User::class, 'id', 'vendor_id');
    }

    public function activity()
    {
        return $this->hasOne(ActivityType::class, 'id', 'activity_id');
    }

    public function vendordata()
    {
        return $this->hasOne(VendorDetailsModel::class, 'user_id', 'vendor_id');
    }

    public function order_product()
    {
        return $this->hasMany(OrderProductsModel::class, 'order_id', 'order_id');
    }

    static function tickets($order_id)
    {
        return DB::table('tickets')->where('order_id', $order_id)->get();
    }

    public function ref_code_history()
    {
        return $this->hasOne(RefHistory::class, 'id', 'ref_history_id');
    }

    public static function order_list($vendor_id)
    {

        $list = OrderModel::select('order_products.*', 'orders.*', 'user_address.address', 'user_address.phone', DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->join('res_users', 'res_users.id', '=', 'orders.user_id')
            ->leftjoin('user_address', 'user_address.user_id', '=', 'orders.user_id')
            ->orderBy('orders.order_id', 'desc')
            ->distinct('orders.order_id')
            ->where('vendor_id', $vendor_id)->paginate(10);
        if ($list->total()) {
            foreach ($list->items() as $key => $row) {

                $list->items()[$key]->tickets = OrderModel::tickets($row->id);
                $list->items()[$key]->product_name = OrderProductsModel::product_name($row->product_id, $row->product_type);
                $list->items()[$key]->vendor_total = DB::table('order_products')->where('vendor_id', $vendor_id)->where('order_id', $row->order_id)->sum('total');

            }
        }
        return $list;
    }

    public static function order_details($vendor_id, $order_id)
    {

        $list = OrderModel::select('order_products.*', 'orders.*', 'user_address.address', 'user_address.phone', DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"))->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->join('res_users', 'res_users.id', '=', 'orders.user_id')
            ->leftjoin('user_address', 'user_address.user_id', '=', 'orders.user_id')
            ->where('orders.order_id', $order_id)
            ->distinct('orders.order_id')
            ->where('vendor_id', $vendor_id)->paginate(10);
        if ($list->total()) {
            foreach ($list->items() as $key => $row) {
                $filter = ['order_id' => $order_id, 'order_products.vendor_id' => $vendor_id];
                $list->items()[$key]->tickets = OrderModel::tickets($row->id);
                $list->items()[$key]->vendor_total = DB::table('order_products')->where('vendor_id', $vendor_id)->where('order_id', $row->order_id)->sum('total');

                $order_products = OrderProductsModel::product_details($filter);
                $order_events = OrderProductsModel::events_details($filter);
                $order_services = OrderProductsModel::services_details($filter);
                $order_packages = OrderProductsModel::packages_details($filter);
                $list->items()[$key]->products = process_product_data($order_products);
                $list->items()[$key]->events = process_events_data($order_events);
                $list->items()[$key]->services = process_service_data($order_services);
                $list->items()[$key]->packages = process_package_data($order_packages);


            }
        }
        return $list;
    }

    public static function order_details_email($order_id)
    {

        $list = OrderModel::select('order_products.*', 'orders.*', 'user_address.address', 'user_address.phone', DB::raw("CONCAT(res_users.first_name,' ',res_users.last_name) as customer_name"), 'res_users.firebase_user_key', 'res_users.fcm_token')->join('order_products', 'order_products.order_id', '=', 'orders.order_id')
            ->join('res_users', 'res_users.id', '=', 'orders.user_id')
            ->leftjoin('user_address', 'user_address.id', '=', 'orders.address_id')
            ->where('orders.order_id', $order_id)
            ->distinct('orders.order_id')
            ->paginate(10);
        if ($list->total()) {
            foreach ($list->items() as $key => $row) {
                $filter = ['order_id' => $order_id];
                $list->items()[$key]->tickets = OrderModel::tickets($row->id);
                $list->items()[$key]->vendor_total = DB::table('order_products')->where('order_id', $row->order_id)->sum('total');

                $order_products = OrderProductsModel::product_details($filter);
                $order_events = OrderProductsModel::events_details($filter);
                $order_services = OrderProductsModel::services_details($filter);
                $order_packages = OrderProductsModel::packages_details($filter);
                $list->items()[$key]->products = process_product_data($order_products);
                $list->items()[$key]->events = process_events_data($order_events);
                $list->items()[$key]->services = process_service_data($order_services);
                $list->items()[$key]->packages = process_package_data($order_packages);


            }
        }
        return $list;
    }

    public static function get_orders($filter = [])
    {
        $data = DB::table('orders')
            ->orderBy('orders.order_id', 'desc')
            ->select('orders.*');
        if (!empty($filter['user_id'])) {
            $data->where('orders.user_id', $filter['user_id']);
        }
        if (!empty($filter['status'])) {
            $data->where('orders.status', $filter['status']);
        }
        if (!empty($filter['type'])) {
            $data->whereIn('orders.oder_type', $filter['type']);
        }
        return $data;
    }

    public static function get_order_detail($order_id)
    {
        return DB::select("select 
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
                            campaigns_draw_date as draw_date,
    
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
    
                            product_order_history.unit_price,
                            product_order_history.unit_shipping_charge,
                            product_order_history.product_total_shipping_charge,	
                            product_order_history.product_total_tax,	
                            product_order_history.product_sub_price,
                            product_order_history.product_total,
                            product_order_history.order_block_id,
                            product_order_history.purchase_qty,
                            product_order_history.deliver_status,
                            product_order_history.is_donate,
                            campaigns.is_spinner,
                            product_order_history.history_id,
    
                            pv_str.product_attr_variation,
                            pv_str.product_attr_variation_arabic,
                            pv_str.attribute_ids,
                            pv_str.attribute_values_ids,
                            product_order_details.donation,
                            product_order_details.order_placed_date,
                            
                            spinner.prize
    
                        from product_order_history 
                        left join product_order_details on product_order_details.order_block_id = product_order_history.order_block_id
                        left join product on product.product_id = product_order_history.product_id	
                        left join campaigns on campaigns.product_id = product_order_history.product_id	
                        left join product_attribute on product_attribute.product_attribute_id = product_order_history.product_attribute_id
                        left join seller_details on seller_details.user_id = product.product_vender_id
                        left join user_spinner_history spinner on spinner.history_id=product_order_history.history_id
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
                        where product_order_history.order_block_id = {$order_id} order by product_order_details.product_order_id");
    }

    public static function get_order_details($filter = [])
    {
        $data = DB::table('orders')
            ->select('orders.*');
        if (!empty($filter['user_id'])) {
            $data->where('orders.user_id', $filter['user_id']);
        }
        if (!empty($filter['order_id'])) {
            $data->where('orders.order_id', $filter['order_id']);
        }

        return $data;
    }
}
