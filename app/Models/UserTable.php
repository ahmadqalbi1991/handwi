<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Laravel\Passport\HasApiTokens;


class UserTable extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $table = 'user_table';
    protected $primaryKey = 'user_id';
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'user_first_name',
        'user_last_name',
        'user_country_id',
        'user_email_id',
        'user_password',
        'user_gender',
        'user_status',
        'user_device_token',
        'user_device_type',
        'user_created_by',
        'user_deleted',
        'user_type',
        'image',
        'facebook_link',
        'twitter_link',
        'instagram_link',
        'snapchat_link',
        'telegram_link',
        'login_type',
        'background_image',
        'fcm_token',
        'fcm_followers_group',
        'about_me',
        'social_key',
        'user_custom_id',
        'specification',
        'access_token',
        'last_login',
        'user_access_token',
        'dial_code',
        'phone_number',
        'referal_code',
        'invited_user_id',
        'user_points',
        'used_points',
        'firebase_user_key',
        'user_middle_name',
        'user_phone_otp',
        'phone_verified',
        'user_address',
        'is_social'
    ];

    protected $hidden = ['user_password'];

    public function getNameAttribute() {
        return $this->user_first_name . ' ' . $this->user_last_name;
    }

    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public static function getUserData($select, $where) {
        return self::select($select)->where($where)->first();
    }

    public static function getUserByEmail($email, $lang_code = 1) {
        return DB::table('user_table')
            ->select('user_table.*', 'country.country_name', 'country.country_currency as currency')
            ->leftJoin('country', function ($join) use ($lang_code) {
                $join->on('country.country_id', '=', 'user_table.user_country_id')
                    ->where('country.country_language_code', '=', $lang_code);
            })
            ->where('user_table.user_email_id', $email)
            ->first();
    }

    public static function updateUser($data, $user_id) {
        self::where('user_id', $user_id)->update($data);
    }

    public static function getUserInfoByTicket($data)
    {
        return DB::table('draw_slip')
            ->join('user_table', 'draw_slip.user_id', '=', 'user_table.user_id')
            ->select('draw_slip.campaign_id',
                'user_table.user_id',
                'user_table.user_first_name',
                'user_table.user_last_name',
                'user_table.user_email_id')
            ->where('draw_slip.draw_slip_number', $data['draw_slip_number'])
            ->where('draw_slip.campaign_id', $data['campaign_id'])
            ->first();
    }

    public static function getUserAddressByAddressId($shippingId, $langCode = "1") {
        return DB::table('user_shiping_details')
            ->select(
                'user_shiping_details.*',
                'user_shiping_details.first_name as s_first_name',
                'user_shiping_details.middle_name as s_middle_name',
                'user_shiping_details.last_name as s_last_name'
            )
            ->leftJoin('user_table', 'user_table.user_id', '=', 'user_shiping_details.user_shiping_details_user_id')
            ->leftJoin('country', function ($join) use ($langCode) {
                $join->on('country.country_id', '=', 'user_shiping_details.user_shiping_country_id')
                    ->where('country.country_language_code', '=', $langCode);
            })
            ->leftJoin('city', function ($join) use ($langCode) {
                $join->on('city.city_id', '=', 'user_shiping_details.user_shiping_details_city')
                    ->where('city.city_language_code', '=', $langCode);
            })
            ->where('user_shiping_details.user_shiping_details_id', $shippingId)
            ->orderBy('user_shiping_details.user_shiping_details_id', 'desc')
            ->first();
    }

    public static function checkShippingExists($shipping_address_id)
    {
        return DB::table('product_order_details')
            ->where('shipping_address_id', $shipping_address_id)
            ->count();
    }

    public static function deleteUserAddress($shippingAddressId)
    {
        return DB::table('user_shiping_details')
            ->where('user_shiping_details_id', $shippingAddressId)
            ->delete();
    }

    public static function getFavourites($user_id = 0, $lang_code = "1", $filter = "", $sort = "") {
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
                                        campaigns_draw_date,
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
                                        favourate.id as favourate_id,
                                        favourate.favourate_added_time,
                                        pv_str.product_attr_variation,
                                        pv_str.product_attr_variation_arabic,
                
                                        pv_str.attribute_ids,
                                        pv_str.attribute_values_ids,
                                        (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                        extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts,
                                        (select sum(purchase_qty) from temp_product_order_history where temp_product_order_history.product_id = product.product_id group by temp_product_order_history.product_id limit 1 ) as product_on_process,
                                        (select sum(purchase_qty) from product_order_history where product_order_history.product_id = product.product_id  group by product_order_history.product_id limit 1) as product_order_placed ,
                                        (select product_attribute.stock_quantity from product_attribute where product_id=product.product_id limit 1) as stock_quantity
                                from favourate 
                                inner join product on product.product_id = favourate.product_id	
                                inner join campaigns on campaigns.product_id = favourate.product_id	
                                left join product_attribute on  product_attribute.product_attribute_id = favourate.product_attribute_id
                left join (select 
                                product_variations.product_attribute_id, 
                                string_agg(attribute_values::text, ', ') as product_attr_variation, 
                                string_agg(attribute_values_arabic::text, ', ') as product_attr_variation_arabic,
                                string_agg(product_variations.attribute_id::text, ', ') as attribute_ids,
                                string_agg(product_variations.attribute_values_id::text, ', ') as attribute_values_ids
                            from product_variations 
                            left join attribute_values on attribute_values.attribute_values_id = product_variations.attribute_values_id
                            left join attribute on attribute.attribute_id = product_variations.attribute_id 
                            group by product_variations.product_attribute_id) pv_str on pv_str.product_attribute_id = favourate.product_attribute_id 
                                where product.product_status=1 and campaigns.campaigns_status = 1 and favourate.user_id = '{$user_id}'  {$filter} order by favourate.id	desc");
    }

    public static function getUserDefaultAddress($user_id, $lang_code = "1")
    {
        return DB::table('user_shiping_details')
            ->select(
                'user_shiping_details.*',
                'user_shiping_details.first_name as s_first_name',
                'user_shiping_details.middle_name as s_middle_name',
                'user_shiping_details.last_name as s_last_name',
                'city.city_name',
                'country.country_name'
            )
            ->leftJoin('user_table', 'user_table.user_id', '=', 'user_shiping_details.user_shiping_details_user_id')
            ->leftJoin('country', function ($join) use ($lang_code) {
                $join->on('country.country_id', '=', 'user_shiping_details.user_shiping_country_id')
                    ->where('country_language_code', $lang_code);
            })
            ->leftJoin('city', function ($join) use ($lang_code) {
                $join->on('city.city_id', '=', 'user_shiping_details.user_shiping_details_city')
                    ->where('city_language_code', $lang_code);
            })
            ->where('user_shiping_details_user_id', $user_id)
            ->where('default_address_status', 1)
            ->first();
    }

    public static function updateUserAddress($data, $condition)
    {
        $affectedRows = DB::table('user_shiping_details')
            ->where($condition)
            ->update($data);

        return $affectedRows;
    }

    public static function createUserAddress($data)
    {
        $insertedId = DB::table('user_shiping_details')
            ->insertGetId($data, 'user_shiping_details_id');

        return $insertedId;
    }

    public static function getUserAddress($user_id, $lang_code = "1")
    {
        return DB::table('user_shiping_details')
            ->select(
                'user_shiping_details.*',
                'user_shiping_details.first_name as s_first_name',
                'user_shiping_details.middle_name as s_middle_name',
                'user_shiping_details.last_name as s_last_name',
                'city.city_name',
                'country.country_name'
            )
            ->leftJoin('user_table', 'user_table.user_id', '=', 'user_shiping_details.user_shiping_details_user_id')
            ->leftJoin('country', function ($join) use ($lang_code) {
                $join->on('country.country_id', '=', 'user_shiping_details.user_shiping_country_id')
                    ->where('country_language_code', $lang_code);
            })
            ->leftJoin('city', function ($join) use ($lang_code) {
                $join->on('city.city_id', '=', 'user_shiping_details.user_shiping_details_city')
                    ->where('city_language_code', $lang_code);
            })
            ->where('user_shiping_details_user_id', $user_id)
            ->orderBy('user_shiping_details_id', 'desc')
            ->get();
    }

}
