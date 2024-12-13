<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    public $timestamps = false;
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'product_type',
        'product_desc_short',
        'product_desc_short_arabic',
        'product_desc_full_arabic',
        'product_desc_full',
        'product_sales_from',
        'product_sales_to',
        'product_featured_image',
        'product_tag',
        'product_created_by',
        'product_updated_by',
        'product_updated_date',
        'product_updated_date',
        'product_status',
        'product_deleted',
        'product_name',
        'product_variation_type',
        'product_taxable',
        'product_vender_id',
        'product_image',
        'product_unique_iden',
        'cash_points',
        'offer_enabled',
        'deal_enabled',
        'is_today_offer',
        'today_offer_date',
        'thanku_perc',
        'custom_status',
        'featured_product',
    ];

    public static function findProduct($id) {
        return DB::table('product as p')
            ->select('*')
            ->leftjoin('user_table as u', 'u.user_id', 'p.product_vender_id')
            ->leftjoin('seller_details as s', 'u.user_id', 's.user_id')
            ->join('campaigns as c', 'c.product_id', 'p.product_id')
            ->join('product_attribute as pa', 'pa.product_id', 'p.product_id')
            ->where('p.product_id', $id)
            ->first();
    }

    public static function getProducts($request) {
        return DB::table('product as p')
            ->select('*')
            ->leftjoin('user_table as u', 'u.user_id', 'p.product_vender_id')
            ->leftjoin('seller_details as s', 'u.user_id', 's.user_id')
            ->join('campaigns as c', 'c.product_id', 'p.product_id')
            ->join('product_attribute as pa', 'pa.product_id', 'p.product_id')
            ->when($request->country_id, function ($q) use ($request) {
                return $q->where('c.country_id', $request->country_id);
            })
            ->when($request->price_from, function ($q) use ($request) {
                return $q->where('pa.sale_price', '>=', $request->price_from);
            })
            ->when($request->price_to, function ($q) use ($request) {
                return $q->where('pa.sale_price', '<=', $request->price_to);
            })
            ->orderBy('p.product_id', 'desc')
            ->get();
    }

    public static function getAllProductsComingSoon ($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
        $userTimezone = USERTIMEZONE;

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
                                    product_attribute.image,
                                    product_attribute.shipping_note,
                                    product.product_image,
                                    favourate.id as favourate_id,
                                    favourate.favourate_added_time,
                                    pv_str.product_attr_variation,
                    pv_str.product_attr_variation_arabic,

                    pv_str.attribute_ids,
                    pv_str.attribute_values_ids,
                                    (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                    extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                    extract(epoch from now()) as db_uts,
                                    (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                    (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed,
                                    (select product_attribute.stock_quantity  from product_attribute where product_id=product.product_id limit 1) as stock_quantity
                            from campaigns 
                            inner join product on product.product_id = campaigns.product_id	
                            left join product_attribute on product_attribute.product_id = product.product_id 
                            and product_attribute.product_attribute_id = (select 
                                                                                min(pa.product_attribute_id) 
                                                                        from product_attribute pa 
                                                                        where pa.product_id = product.product_id) 
                            left join favourate on favourate.product_id = product.product_id 
                                        and favourate.user_id = '{$user_id}' 
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
                            where  product_status=1 
                            and campaigns_status = 0 
                           
                            and (((now() AT TIME ZONE '{$userTimezone}')::timestamp ) < ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp)) and campaigns.country_id = {$country_id} {$filter}");
    }

    public static function getActiveAppSliders($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $timezone = USERTIMEZONE;
        $t_order_stock_timeout = TOrderStockTimeout;
        return DB::select("select 
                                        campaigns_id,
                                        campaigns_title,                                        
                        pv_str.product_attr_variation_arabic,
                        pv_str.attribute_ids,
                        pv_str.attribute_values_ids,
                        slider_images_app.si_id,
                        slider_images_app.si_name,
                        slider_images_app.si_target,
                        slider_images_app.si_url,
                        slider_images_app.si_image,
                        slider_images_app.si_type_id,
                        slider_images_app.si_type,
                                                                (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        extract(epoch from now()) as db_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= (NOW()AT TIME ZONE '{$timezone}') - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                                from campaigns 
                                inner join product on product.product_id = campaigns.product_id	
                                left join product_attribute on product_attribute.product_id = product.product_id 
                                and product_attribute.product_attribute_id = (select 
                                                                                    min(pa.product_attribute_id) 
                                                                            from product_attribute pa 
                                                                            where pa.product_id = product.product_id)
                                 join slider_images_app on  product_attribute.product_id =slider_images_app.product_id  and  product_attribute.product_attribute_id =slider_images_app.product_attribute_id  and  slider_images_app.si_language_code=1  and  slider_images_app.si_status=1                                       
                                left join favourate on favourate.product_id = product.product_id 
                                            and favourate.user_id = '{$user_id}' 
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
                                where  product_status=1 
                                        and campaigns_status = 0 
                                        and  ((now() AT TIME ZONE '{$timezone}')::timestamp ) > ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp )
                                        and campaigns.country_id = {$country_id} {$filter}");
    }

    public static function getFaq($countryId, $langCode = "1") {
        if ($langCode == "2") {
            $select = DB::raw("CASE WHEN LENGTH(TRIM(faq_title_arabic)) > 0 THEN faq_title_arabic ELSE faq_title END as title, 
                           CASE WHEN LENGTH(TRIM(faq_description_arabic)) > 0 THEN faq_description_arabic ELSE faq_description END as description");
        } else {
            $select = DB::raw("faq_title as title, faq_description as description");
        }

        return DB::table('faq')
            ->select($select)
            ->where('status', '1')
            ->where('country_id', $countryId)
            ->get();
    }

    public static function getAllProducts($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "", $limit = 10, $offset = 0, $allow_pagination = false) {
        $t_order_stock_timeout = 3600;
        $userTimezone = USERTIMEZONE;
        $limit_query = '';

        if ($allow_pagination) {
            $limit_query = 'LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

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
                                        campaigns.is_featured,
                                        campaigns.country_id,
                                        is_spinner,
                                        draw_date,
                                        campaigns.is_vip,
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
                                        product_attribute.image,
                                        product_attribute.shipping_note,
                                        product.product_image,
                                        favourate.id as favourate_id,
                                        favourate.favourate_added_time,
                                        pv_str.product_attr_variation,
                        pv_str.product_attr_variation_arabic,

                        pv_str.attribute_ids,
                        pv_str.attribute_values_ids,
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        extract(epoch from now()) as db_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= (NOW()AT TIME ZONE '{$userTimezone}') - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed,
                                        (select product_attribute.stock_quantity  from product_attribute where product_id=product.product_id limit 1) as stock_quantity
                                from campaigns 
                                inner join product on product.product_id = campaigns.product_id	
                                left join product_attribute on product_attribute.product_id = product.product_id 
                                and product_attribute.product_attribute_id = (select 
                                                                                    min(pa.product_attribute_id) 
                                                                            from product_attribute pa 
                                                                            where pa.product_id = product.product_id) 
                                left join favourate on favourate.product_id = product.product_id 
                                            and favourate.user_id = '{$user_id}' 
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
                                where  product_status=1 
                                        and campaigns_status IN (1,2)
                                        and  ((now() AT TIME ZONE '{$userTimezone}')::timestamp ) > ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp ) 
                                        and  ((now() AT TIME ZONE '{$userTimezone}')::timestamp ) < (campaigns_draw_date::timestamp ) 
                                        and campaigns.country_id = {$country_id} {$filter} {$sort} {$limit_query}");
    }

    public static function getComingProductsByCategoryId($country_id, $category_id, $user_id) {
        $userTimezone = "Etc/GMT";
        $t_order_stock_timeout = 3600;

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
                                    product_attribute.image,
                                    product_attribute.shipping_note,
                                    product.product_image,
                                    favourate.id as favourate_id,
                                    favourate.favourate_added_time,
                                    pv_str.product_attr_variation,
                                    pv_str.product_attr_variation_arabic,
            
                                    pv_str.attribute_ids,
                                    pv_str.attribute_values_ids,
                                    (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                    extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                    extract(epoch from now()) as db_uts,
                                    (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                    (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                            from campaigns 
                            inner join product on product.product_id = campaigns.product_id	
                            left join product_attribute on product_attribute.product_id = product.product_id 
                            and product_attribute.product_attribute_id = (select 
                                                                                min(pa.product_attribute_id) 
                                                                        from product_attribute pa 
                                                                        where pa.product_id = product.product_id) 
                            left join product_category on product_category.product_id = product.product_id
                            left join favourate on favourate.product_id = product.product_id 
                                        and favourate.user_id = '{$user_id}' 
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
                            where product_status=1 
                                    and category_id={$category_id} 
                                    and campaigns_status = 0 
                                    and  (( now() AT TIME ZONE '{$userTimezone}' + interval '7' day )::timestamp )  > ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp)
                                    and (((now() AT TIME ZONE '{$userTimezone}')::timestamp ) < ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp )) and campaigns.country_id = {$country_id}");
    }

    public static function getProduct ($product_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
        return DB::table('campaigns')
            ->join('product', 'product.product_id', '=', 'campaigns.product_id')
            ->leftJoin('product_attribute', function($join) {
                $join->on('product_attribute.product_id', '=', 'product.product_id')
                    ->where('product_attribute.product_attribute_id', '=', function($query) {
                        $query->selectRaw('MIN(pa.product_attribute_id)')
                            ->from('product_attribute as pa')
                            ->whereColumn('pa.product_id', 'product.product_id');
                    });
            })
            ->leftJoin('favourate', function($join) use ($user_id) {
                $join->on('favourate.product_id', '=', 'product.product_id')
                    ->where('favourate.user_id', $user_id);
            })
            ->leftJoin(DB::raw('(SELECT 
                                product_variations.product_attribute_id, 
                                STRING_AGG(attribute_values::text, \', \') AS product_attr_variation, 
                                STRING_AGG(attribute_values_arabic::text, \', \') AS product_attr_variation_arabic,
                                STRING_AGG(product_variations.attribute_id::text, \', \') AS attribute_ids,
                                STRING_AGG(product_variations.attribute_values_id::text, \', \') AS attribute_values_ids
                             FROM product_variations 
                             LEFT JOIN attribute_values ON attribute_values.attribute_values_id = product_variations.attribute_values_id
                             LEFT JOIN attribute ON attribute.attribute_id = product_variations.attribute_id 
                             GROUP BY product_variations.product_attribute_id) pv_str'), 'pv_str.product_attribute_id', '=', 'product_attribute.product_attribute_id')
            ->select([
                'campaigns.campaigns_id',
                'campaigns.campaigns_title',
                'campaigns.campaigns_date',
                'campaigns.campaigns_time',
                'campaigns.campaigns_date_start',
                'campaigns.campaigns_time_start',
                'campaigns.campaigns_qty',
                'campaigns.campaigns_desc',
                'campaigns.campaigns_image',
                'campaigns.campaigns_image2',
                'campaigns.campaigns_title_arabic',
                'campaigns.campaigns_desc_arabic',
                'campaigns.country_id',
                'campaigns.campaigns_draw_date',
                'campaigns.campaigns_status',
                'campaigns.is_spinner',
                'campaigns.draw_date',
                'campaigns.is_vip',
                'product.product_id',
                'product.product_name',
                'product.product_name_arabic',
                'product.product_type',
                'product.product_desc_full',
                'product.product_desc_full_arabic',
                'product.product_desc_short',
                'product.product_desc_short_arabic',
                'product.product_sale_from',
                'product.product_sale_to',
                'product.product_tag',
                'product.product_status',
                'product.product_variation_type',
                'product.product_taxable',
                'product.product_unique_iden',
                'product.product_brand_id',
                'product_attribute.product_attribute_id',
                'product_attribute.manage_stock',
                'product_attribute.allow_back_order',
                'product_attribute.stock_status',
                'product_attribute.sold_individually',
                'product_attribute.weight',
                'product_attribute.length',
                'product_attribute.height',
                'product_attribute.width',
                'product_attribute.shipping_class',
                'product_attribute.sale_price',
                'product_attribute.regular_price',
                'product_attribute.image',
                'product_attribute.shipping_note',
                'product.product_image',
                'favourate.id as favourate_id',
                'favourate.favourate_added_time',
                'pv_str.product_attr_variation',
                'pv_str.product_attr_variation_arabic',
                'pv_str.attribute_ids',
                'pv_str.attribute_values_ids',
                DB::raw("(campaigns_date || ' ' || campaigns_time)::timestamp as campaigns_expiry"),
                DB::raw("EXTRACT(epoch FROM (campaigns_date || ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts"),
                DB::raw("EXTRACT(epoch FROM now()) as db_uts"),
                DB::raw("(SELECT SUM(purchase_qty) FROM temp_product_order_history WHERE temp_product_order_history.product_id = product.product_id AND created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' GROUP BY temp_product_order_history.product_id LIMIT 1) as product_on_process"),
                DB::raw("(SELECT SUM(purchase_qty) FROM product_order_history WHERE product_order_history.product_id = product.product_id GROUP BY product_order_history.product_id LIMIT 1) as product_order_placed"),
                DB::raw("(SELECT product_attribute.stock_quantity FROM product_attribute WHERE product_id = product.product_id LIMIT 1) as stock_quantity")
            ])
            ->where('product.product_id', $product_id)
            ->first();
    }

    public static function getProductByAttributeIdForProductInfo($product_id, $product_attribute_id, $user_id = 0, $lang_code = "1", $filter = "")
    {
        $t_order_stock_timeout = 3600; // Example value, adjust as needed

        return DB::table('campaigns')
            ->join('product', 'product.product_id', '=', 'campaigns.product_id')
            ->leftJoin('product_attribute', function($join) use ($product_attribute_id) {
                $join->on('product_attribute.product_id', '=', 'product.product_id')
                    ->where('product_attribute.product_attribute_id', $product_attribute_id);
            })
            ->leftJoin('favourate', function($join) use ($user_id) {
                $join->on('favourate.product_id', '=', 'product.product_id')
                    ->where('favourate.user_id', $user_id);
            })
            ->leftJoin(DB::raw('(SELECT 
                                product_variations.product_attribute_id, 
                                STRING_AGG(attribute_values::text, \', \') AS product_attr_variation, 
                                STRING_AGG(attribute_values_arabic::text, \', \') AS product_attr_variation_arabic,
                                STRING_AGG(product_variations.attribute_id::text, \', \') AS attribute_ids,
                                STRING_AGG(product_variations.attribute_values_id::text, \', \') AS attribute_values_ids
                             FROM product_variations 
                             LEFT JOIN attribute_values ON attribute_values.attribute_values_id = product_variations.attribute_values_id
                             LEFT JOIN attribute ON attribute.attribute_id = product_variations.attribute_id 
                             GROUP BY product_variations.product_attribute_id) pv_str'), 'pv_str.product_attribute_id', '=', 'product_attribute.product_attribute_id')
            ->select([
                'campaigns.campaigns_id',
                'campaigns.campaigns_title',
                'campaigns.campaigns_date',
                'campaigns.campaigns_time',
                'campaigns.campaigns_date_start',
                'campaigns.campaigns_time_start',
                'campaigns.campaigns_qty',
                'campaigns.campaigns_desc',
                'campaigns.campaigns_image',
                'campaigns.campaigns_image2',
                'campaigns.campaigns_title_arabic',
                'campaigns.campaigns_desc_arabic',
                'campaigns.country_id',
                'campaigns.campaigns_status',
                'campaigns.is_spinner',
                'campaigns.draw_date',
                'campaigns.is_vip',
                'product.product_id',
                'product.product_name',
                'product.product_name_arabic',
                'product.product_type',
                'product.product_desc_full',
                'product.product_desc_full_arabic',
                'product.product_desc_short',
                'product.product_desc_short_arabic',
                'product.product_sale_from',
                'product.product_sale_to',
                'product.product_tag',
                'product.product_status',
                'product.product_variation_type',
                'product.product_taxable',
                'product.product_unique_iden',
                'product.product_brand_id',
                'product_attribute.product_attribute_id',
                'product_attribute.manage_stock',
                'product_attribute.allow_back_order',
                'product_attribute.stock_status',
                'product_attribute.sold_individually',
                'product_attribute.weight',
                'product_attribute.length',
                'product_attribute.height',
                'product_attribute.width',
                'product_attribute.shipping_class',
                'product_attribute.sale_price',
                'product_attribute.regular_price',
                'product_attribute.image',
                'product_attribute.shipping_note',
                'product_attribute.image as product_image',
                'favourate.id as favourate_id',
                'favourate.favourate_added_time',
                'pv_str.product_attr_variation',
                'pv_str.product_attr_variation_arabic',
                'pv_str.attribute_ids',
                'pv_str.attribute_values_ids',
                DB::raw("(campaigns_date || ' ' || campaigns_time)::timestamp as campaigns_expiry"),
                DB::raw("EXTRACT(epoch FROM (campaigns_date || ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts"),
                DB::raw("EXTRACT(epoch FROM now()) as db_uts"),
                DB::raw("(SELECT SUM(purchase_qty) FROM temp_product_order_history WHERE temp_product_order_history.product_id = product.product_id AND created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' GROUP BY temp_product_order_history.product_id LIMIT 1) as product_on_process"),
                DB::raw("(SELECT SUM(purchase_qty) FROM product_order_history WHERE product_order_history.product_id = product.product_id GROUP BY product_order_history.product_id LIMIT 1) as product_order_placed"),
                DB::raw("(SELECT product_attribute.stock_quantity FROM product_attribute WHERE product_id = product.product_id LIMIT 1) as stock_quantity")
            ])
            ->where('product.product_id', $product_id)
            ->where('product_attribute.product_attribute_id', $product_attribute_id)
            ->first();
    }

    public static function getClosedCampaignByCategoryId($country_id, $category_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
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
                                product_attribute.image,
                                product_attribute.shipping_note,
                                product.product_image,
                                favourate.id as favourate_id,
                                favourate.favourate_added_time,
                                pv_str.product_attr_variation,
                                pv_str.product_attr_variation_arabic,
        
                                pv_str.attribute_ids,
                                pv_str.attribute_values_ids,
                                (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                extract(epoch from now()) as db_uts,
                                (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                        from campaigns 
                        inner join product on product.product_id = campaigns.product_id	
                        left join product_attribute on product_attribute.product_id = product.product_id 
                        and product_attribute.product_attribute_id = (select 
                                                                            min(pa.product_attribute_id) 
                                                                    from product_attribute pa 
                                                                    where pa.product_id = product.product_id) 
                        left join product_category on product_category.product_id = product.product_id
                        left join favourate on favourate.product_id = product.product_id 
                                    and favourate.user_id = '{$user_id}' 
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
                        where product_status=1 
                                and category_id={$category_id} 
                                and campaigns_status = 2                                       
                                and campaigns.country_id = {$country_id} ");
    }

    public static function getAllClosedCampaigns($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $t_order_stock_timeout = 3600;
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
                            product.product_image,
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
                            product_attribute.image,
                            product_attribute.shipping_note,
                            product.product_image,
                            favourate.id as favourate_id,
                            favourate.favourate_added_time,
                            pv_str.product_attr_variation,
            pv_str.product_attr_variation_arabic,

            pv_str.attribute_ids,
            pv_str.attribute_values_ids,
                            (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                            extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                            extract(epoch from now()) as db_uts,
                            (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                            (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                    from campaigns 
                    inner join product on product.product_id = campaigns.product_id	
                    left join product_attribute on product_attribute.product_id = product.product_id 
                    and product_attribute.product_attribute_id = (select 
                                                                        min(pa.product_attribute_id) 
                                                                from product_attribute pa 
                                                                where pa.product_id = product.product_id) 
                    left join favourate on favourate.product_id = product.product_id 
                                and favourate.user_id = '{$user_id}' 
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
                    where  product_status=1 
                            and campaigns_status = 2                                    
                            and campaigns.country_id = {$country_id} {$filter}");
    }

    public static function getProductAttributeId($productId) {
        return DB::table('product_attribute')
            ->select('product_attribute_id')
            ->where('product_id', $productId)
            ->get();
    }

    public static function getProductStock($productId)
    {
        return DB::table('product_attribute')
            ->select('stock_quantity')
            ->where('product_id', $productId)
            ->get();
    }

    public static function getAllProductsSearch($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
        $userTimezone = USERTIMEZONE;
        $t_order_stock_timeout = TOrderStockTimeout;
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
                                        product_attribute.image,
                                        product_attribute.shipping_note,
                                        product.product_image,
                                        favourate.id as favourate_id,
                                        favourate.favourate_added_time,
                                        pv_str.product_attr_variation,
                        pv_str.product_attr_variation_arabic,

                        pv_str.attribute_ids,
                        pv_str.attribute_values_ids,
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        extract(epoch from now()) as db_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= (NOW()AT TIME ZONE '{$userTimezone}') - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                                from campaigns 
                                inner join product on product.product_id = campaigns.product_id	
                                left join product_attribute on product_attribute.product_id = product.product_id 
                                and product_attribute.product_attribute_id = (select 
                                                                                    min(pa.product_attribute_id) 
                                                                            from product_attribute pa 
                                                                            where pa.product_id = product.product_id) 
                                left join favourate on favourate.product_id = product.product_id 
                                            and favourate.user_id = '{$user_id}' 
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
                                where  product_status=1 
                                        and campaigns_status = 0 
                                        and  ((now() AT TIME ZONE '{$userTimezone}')::timestamp ) BETWEEN ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp )   AND ((campaigns_date|| ' ' || campaigns_time)::timestamp )                                       
                                        and campaigns.country_id = {$country_id} {$filter}
                                        or (((now()AT TIME ZONE '{$userTimezone}')::timestamp < (campaigns_date_start|| ' ' || campaigns_time_start)::timestamp) and campaigns_status = 0 and campaigns.country_id = {$country_id} {$filter})");
    }

    public static function getWebSliders($langCode = '1', $countryId)
    {
        return DB::table('banner_images_app')
            ->select('*')
            ->where('bi_status', 1)
//            ->where('si_language_code', $langCode)
//            ->where('country_id', $countryId)
            ->get();
    }

    public static function getAllProductsWithAttributes($country_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "",$product_id=NULL, $product_attribute_id=NULL) {
        $userTimezone = "Etc/GMT";
        $t_order_stock_timeout = 3600;

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
                                        product.product_image,
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
                                        product_attribute.image,
                                        product_attribute.shipping_note,
                                        product.product_image,
                                        favourate.id as favourate_id,
                                        favourate.favourate_added_time,
                                        pv_str.product_attr_variation,
                        pv_str.product_attr_variation_arabic,

                        pv_str.attribute_ids,
                        pv_str.attribute_values_ids,
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        extract(epoch from now()) as db_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= (NOW()AT TIME ZONE '{$userTimezone}') - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed 
                                from campaigns 
                                inner join product on product.product_id = campaigns.product_id	
                                left join product_attribute on product_attribute.product_id = product.product_id
                                and product_attribute.product_attribute_id = product_attribute.product_attribute_id  
                                left join favourate on favourate.product_id = product.product_id 
                                            and favourate.user_id = '{$user_id}' 
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
                                where  product_status=1 
                                        and campaigns_status = 0 
                                       
                                        and campaigns.country_id = {$country_id} and product_attribute.product_id = {$product_id}
                                        and product_attribute.product_attribute_id = {$product_attribute_id} {$filter} {$sort}");
    }

    public static function getProductSelAttributeVariationCombinations($product_id, $sel_attrib_value_id, $sel_attrib_id) {
        return DB::table('product_variations')
            ->join(DB::table('product_variations')
                ->select('product_attribute_id')
                ->where('attribute_values_id', $sel_attrib_value_id)
                ->where('product_id', $product_id)
                ->as('sel_product_variations'),
                'sel_product_variations.product_attribute_id', '=', 'product_variations.product_attribute_id')
            ->where('product_variations.product_id', $product_id)
            ->where('product_variations.attribute_id', '<>', $sel_attrib_id)
            ->get();
    }

    public static function getProductAttributeVariation($product_id, $product_attribute_id)
    {
        return DB::table('product_variations')
            ->where('product_id', $product_id)
            ->where('product_attribute_id', $product_attribute_id)
            ->get();
    }

    public static function getProductOnSelectedAttributes($product_id, $sel_attributes_values)
    {
        if (count($sel_attributes_values) === 0) {
            return null;
        }

        $query = DB::table('product_variations')
            ->select('product_attribute_id')
            ->where('product_id', $product_id)
            ->whereIn('attribute_values_id', $sel_attributes_values);

        $result = $query->get();
        $resultIds = $result->pluck('product_attribute_id')->toArray();
        $commonIds = array_intersect($resultIds, $sel_attributes_values);

        return $commonIds ? $commonIds : null;
    }

    public static function getProductByAttributeId($product_id, $product_attribute_id, $user_id = 0, $lang_code = "1", $filter = "")
    {
        $t_order_stock_timeout = 3600;
        $subQuery = DB::table('product_variations')
            ->select([
                'product_variations.product_attribute_id',
                DB::raw('string_agg(attribute_values::text, \', \') as product_attr_variation'),
                DB::raw('string_agg(attribute_values_arabic::text, \', \') as product_attr_variation_arabic'),
                DB::raw('string_agg(product_variations.attribute_id::text, \', \') as attribute_ids'),
                DB::raw('string_agg(product_variations.attribute_values_id::text, \', \') as attribute_values_ids')
            ])
            ->leftJoin('attribute_values', 'attribute_values.attribute_values_id', '=', 'product_variations.attribute_values_id')
            ->leftJoin('attribute', 'attribute.attribute_id', '=', 'product_variations.attribute_id')
            ->groupBy('product_variations.product_attribute_id');

        return DB::table('campaigns')
            ->join('product', 'product.product_id', '=', 'campaigns.product_id')
            ->leftJoin('product_attribute', function ($join) {
                $join->on('product_attribute.product_id', '=', 'product.product_id');
            })
            ->leftJoin('favourate', function ($join) use ($user_id) {
                $join->on('favourate.product_id', '=', 'product.product_id')
                    ->where('favourate.user_id', '=', $user_id);
            })
            ->leftJoinSub($subQuery, 'pv_str', function ($join) {
                $join->on('pv_str.product_attribute_id', '=', 'product_attribute.product_attribute_id');
            })
            ->select([
                'campaigns.campaigns_id',
                'campaigns.campaigns_title',
                'campaigns.campaigns_date',
                'campaigns.campaigns_time',
                'campaigns.campaigns_date_start',
                'campaigns.campaigns_time_start',
                'campaigns.campaigns_qty',
                'campaigns.campaigns_desc',
                'campaigns.campaigns_image',
                'campaigns.campaigns_image2',
                'campaigns.campaigns_title_arabic',
                'campaigns.campaigns_desc_arabic',
                'campaigns.country_id',
                'campaigns.campaigns_status',
                'campaigns.is_spinner',
                'campaigns.draw_date',
                'product.product_id',
                'product.product_name',
                'product.product_name_arabic',
                'product.product_type',
                'product.product_desc_full',
                'product.product_desc_full_arabic',
                'product.product_desc_short',
                'product.product_desc_short_arabic',
                'product.product_sale_from',
                'product.product_sale_to',
                'product.product_tag',
                'product.product_status',
                'product.product_variation_type',
                'product.product_taxable',
                'product.product_unique_iden',
                'product.product_brand_id',
                'product_attribute.product_attribute_id',
                'product_attribute.stock_quantity',
                'product_attribute.manage_stock',
                'product_attribute.allow_back_order',
                'product_attribute.stock_status',
                'product_attribute.sold_individually',
                'product_attribute.weight',
                'product_attribute.length',
                'product_attribute.height',
                'product_attribute.width',
                'product_attribute.shipping_class',
                'product_attribute.sale_price',
                'product_attribute.regular_price',
                'product_attribute.image',
                'product_attribute.shipping_note',
                'product_attribute.image as product_image',
                'favourate.id as favourate_id',
                'favourate.favourate_added_time',
                'pv_str.product_attr_variation',
                'pv_str.product_attr_variation_arabic',
                'pv_str.attribute_ids',
                'pv_str.attribute_values_ids',
                DB::raw("(campaigns.campaigns_date || ' ' || campaigns.campaigns_time)::timestamp as campaigns_expiry"),
                DB::raw("extract(epoch from (campaigns.campaigns_date || ' ' || campaigns.campaigns_time)::timestamp) as campaigns_expiry_uts"),
                DB::raw("extract(epoch from now()) as db_uts"),
                DB::raw("(select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1) as product_on_process"),
                DB::raw("(select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id group by product_order_history.product_id limit 1) as product_order_placed")
            ])
            ->where('product.product_id', $product_id)
            ->where('product_attribute.product_attribute_id', $product_attribute_id)
            ->first();
    }

    public static function getMinProductOnSelectedAttribute($product_id, $sel_attribute_value_id)
    {
        return DB::table('product_variations')
            ->select('product_attribute_id')
            ->where('product_id', $product_id)
            ->where('attribute_values_id', $sel_attribute_value_id)
            ->first();
    }

    public static function getProductAttributes($product_id)
    {
        $subQuery = DB::table('product_selected_attributes')
            ->select(
                'product_id',
                'attribute_id',
                'attribute_values_id'
            )
            ->where('product_id', $product_id)
            ->groupBy('attribute_id', 'attribute_values_id', 'product_id');

        return DB::table(DB::raw("({$subQuery->toSql()}) as ps_attrib"))
            ->mergeBindings($subQuery)
            ->leftJoin('attribute_values', 'attribute_values.attribute_values_id', '=', 'ps_attrib.attribute_values_id')
            ->leftJoin('attribute', 'attribute.attribute_id', '=', 'ps_attrib.attribute_id')
            ->select(
                'ps_attrib.product_id',
                'ps_attrib.attribute_id',
                'ps_attrib.attribute_values_id',
                'attribute_values.attribute_value',
                'attribute.attribute_name'
            )
            ->get();
    }

    public static function getProductsByCampaignsIdForAll($country_id, $campaigns_id, $user_id = 0, $lang_code = "1", $filter = "", $sort = "")
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
                     campaigns_image,
                     campaigns_title_arabic,
                     campaigns.country_id,
                     campaigns_status,
                     category_id,
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
                     product_attribute.image,
                     product_attribute.shipping_note,
                     product.product_image,
                     favourate.id as favourate_id,
                     favourate.favourate_added_time,
                     pv_str.product_attr_variation,
                     pv_str.product_attr_variation_arabic,              
                     pv_str.attribute_ids,
                     pv_str.attribute_values_ids,
                     (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                     extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                     extract(epoch from now()) as db_uts,
                     (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id  group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                     (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed ,
                     (select product_attribute.stock_quantity  from product_attribute where product_id=product.product_id limit 1 ) as stock_quantity
             from campaigns 
             inner join product on product.product_id = campaigns.product_id
             left join product_category on product_category.product_id = product.product_id  
             left join product_attribute on product_attribute.product_id = product.product_id 
             and product_attribute.product_attribute_id = (select 
                                                                 min(pa.product_attribute_id) 
                                                         from product_attribute pa 
                                                         where pa.product_id = product.product_id) 
             left join favourate on favourate.product_id = product.product_id 
                         and favourate.user_id = '{$user_id}' 
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
             where product_status=1 
                     and campaigns_id={$campaigns_id} 
                     and campaigns_status = 0                                      
                     and campaigns.country_id = {$country_id}  limit 1 ");
    }


    public static function getProductsByCategoryId($country_id, $category_id = 0, $user_id = 0, $campaigns_id = 0, $lang_code = "1", $filter = "", $sort = "")
    {
        if ($category_id == '') {
            $category_id = 0;
        }

        $userTimezone = "Etc/GMT";
        $t_order_stock_timeout = 3600;

        return DB::select("SELECT 
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
                            campaigns.draw_date,
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
                            product_attribute.image,
                            product_attribute.shipping_note,
                            product.product_image,
                            favourate.id as favourate_id,
                            favourate.favourate_added_time,
                            pv_str.product_attr_variation,
                            pv_str.product_attr_variation_arabic,
                    
                            pv_str.attribute_ids,
                            pv_str.attribute_values_ids,
                            (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                            extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                            extract(epoch from now()) as db_uts,
                            (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and  created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                            (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed, 
                            (select product_attribute.stock_quantity  from product_attribute where product_id=product.product_id limit 1) as stock_quantity
                    from campaigns 
                    inner join product on product.product_id = campaigns.product_id 
                    left join product_attribute on product_attribute.product_id = product.product_id 
                    and product_attribute.product_attribute_id = (select 
                                                                        min(pa.product_attribute_id) 
                                                                from product_attribute pa 
                                                                where pa.product_id = product.product_id) 
                    left join product_category on product_category.product_id = product.product_id
                    left join favourate on favourate.product_id = product.product_id 
                                and favourate.user_id = '{$user_id}' 
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
                    where product_status=1 
                            and category_id={$category_id} 
                            and campaigns_status = 0 
                            and  ((now() AT TIME ZONE '{$userTimezone}')::timestamp )  > ((campaigns_date_start|| ' ' || campaigns_time_start)::timestamp ) 
                            and campaigns.campaigns_id!={$campaigns_id}
                            and campaigns.country_id = {$country_id} {$filter} {$sort} ");
    }

    public static function checkFutureCampaign($user_id = 0, $campaigns_id = 0) {
        $userTimezone = "Etc/GMT";
        $t_order_stock_timeout = 3600;
        return DB::table('campaigns')
            ->select(
                'campaigns.campaigns_id',
                'campaigns.campaigns_title',
                'pv_str.attribute_ids',
                'pv_str.attribute_values_ids',
                DB::raw("(campaigns_date || ' ' || campaigns_time)::timestamp as campaigns_expiry"),
                DB::raw("extract(epoch from (campaigns_date || ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts"),
                DB::raw("extract(epoch from now()) as db_uts"),
                DB::raw("(select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id and created_at >= NOW() - INTERVAL '{$t_order_stock_timeout} seconds' group by temp_product_order_history.product_id limit 1) as product_on_process"),
                DB::raw("(select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id group by product_order_history.product_id limit 1) as product_order_placed")
            )
            ->join('product', 'product.product_id', '=', 'campaigns.product_id')
            ->leftJoin('product_attribute', function ($join) {
                $join->on('product_attribute.product_id', '=', 'product.product_id')
                    ->whereRaw('product_attribute.product_attribute_id = (select min(pa.product_attribute_id) from product_attribute pa where pa.product_id = product.product_id)');
            })
            ->leftJoin('favourate', function ($join) use ($user_id) {
                $join->on('favourate.product_id', '=', 'product.product_id')
                    ->where('favourate.user_id', '=', $user_id);
            })
            ->leftJoin(DB::raw("(select 
                                product_variations.product_attribute_id, 
                                string_agg(attribute_values::text, ', ') as product_attr_variation, 
                                string_agg(attribute_values_arabic::text, ', ') as product_attr_variation_arabic,
                                string_agg(product_variations.attribute_id::text, ', ') as attribute_ids,
                                string_agg(product_variations.attribute_values_id::text, ', ') as attribute_values_ids
                            from product_variations 
                            left join attribute_values on attribute_values.attribute_values_id = product_variations.attribute_values_id
                            left join attribute on attribute.attribute_id = product_variations.attribute_id 
                            group by product_variations.product_attribute_id) as pv_str"), 'pv_str.product_attribute_id', '=', 'product_attribute.product_attribute_id')
            ->where('product.product_status', 1)
            ->where('campaigns.campaigns_status', 0)
            ->where('campaigns.campaigns_id', $campaigns_id)
            ->whereRaw("((now() AT TIME ZONE '{$userTimezone}')::timestamp) < ((campaigns_date_start || ' ' || campaigns_time_start)::timestamp)")
            ->get();
    }

    public static function checkInMyShop($condition) {
        if ($condition) {
            $my_shop = DB::table('my_shop')
                ->select('*')
                ->where($condition)
                ->first();

            if(empty($my_shop)){
                return "0";
            }else{
                return $my_shop->code;
            }
        } else {
            return "0";
        }
    }
}
