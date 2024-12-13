<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class TicketModel extends Model
{
    use HasFactory;

    protected $table = 'product_order_ticket_number';
    protected $primaryKey = 'product_order_ticket_id';

    public static function getMyTickets($id, $limit, $offset, $status) {
        return DB::table('draw_slip as ds')
            ->join('user_table as ut', 'ut.user_id', 'ds.user_id')
            ->join('campaigns as cs', 'cs.campaigns_id', 'ds.campaign_id')
            ->join('product_order_details as pod', 'pod.order_block_id', 'ds.order_block_id')
            ->where('ds.user_id', $id)
            ->where('ds.draw_slip_status', $status)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public static function getUserTicketsAll($user_id) {
        return DB::select("SELECT 
        campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_draw_date,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product_attribute.image as product_image
        FROM product_order_ticket_number 
         join draw_slip on product_order_ticket_number.ticket_number=draw_slip.draw_slip_number
         join product_order_history on product_order_ticket_number.order_block_id=product_order_history.order_block_id
        and product_order_ticket_number.product_id=product_order_history.product_id  
		and product_order_ticket_number.product_attribute_id=product_order_history.product_attribute_id 
        join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status<>0
        join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
        join product_attribute on product_attribute.product_id = product_order_history.product_id and product_attribute.product_attribute_id=
        (select    min(product_attribute.product_attribute_id)  from product_attribute  where product_attribute.product_id = product_order_history.product_id) 
        join product on product.product_id = product_order_history.product_id	
        where product_order_history.user_id='{$user_id}' and campaigns.campaigns_status!='2' and draw_slip.draw_slip_status=0 and product_order_details.delivery_status<>3 group by campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_draw_date,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product_attribute.image order by(product_order_details.order_placed_date::timestamp)desc");
    }

    public static function getOrderTicketNumbers($condition)
    {
        return DB::table('product_order_ticket_number')
            ->select('ticket_number', 'product_id')
            ->where($condition)
            ->get();
    }


    public static function getUserTicketsWon($user_id) {
        return DB::select("SELECT 
        campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_draw_date,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        draw_slip.draw_slip_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product.product_image,
        user_table.user_first_name,
        user_table.user_middle_name,
        user_table.user_last_name,
        user_table.image
        FROM draw_slip 
        join product_order_history on draw_slip.order_block_id=product_order_history.order_block_id
        join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status<>0
        join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
        join product_attribute on product_attribute.product_id = product_order_history.product_id and product_attribute.product_attribute_id=
        (select    min(product_attribute.product_attribute_id)  from product_attribute  where product_attribute.product_id = product_order_history.product_id) 
        join product on product.product_id = product_order_history.product_id
        join user_table on user_table.user_id = {$user_id}	
        where product_order_history.user_id='{$user_id}' and draw_slip.draw_slip_status=1 
         group by campaigns.campaigns_id, 
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_draw_date,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        draw_slip.draw_slip_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product_attribute.image ,
        product.product_image,
        user_table.user_first_name,
        user_table.user_middle_name,
        user_table.user_last_name,
        user_table.image order by(product_order_details.order_placed_date::timestamp)desc");
    }

    public static function getWonTicketNumber($campaignId) {
        return DB::table('draw_slip')
            ->select('*')
            ->join('user_table', 'user_table.user_id', '=', 'draw_slip.user_id')
            ->where('campaign_id', $campaignId)
            ->where('draw_slip_status', 1)
            ->first();
    }

    public static function getCampaign ($user_id, $campaigns_id) {
        return DB::select("select 
                                    campaigns_id,
                                    campaigns_title,
                                    campaigns_date,
                                    campaigns_time,
                                    campaigns_date_start,
                                    campaigns_time_start,
                                    campaigns_draw_date,
                                    campaigns_qty,
                                    campaigns_desc,
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

                                    (campaigns_date|| ' ' || campaigns_time)::timestamp as campaigns_expiry,
                                    extract(epoch from (campaigns_date|| ' ' || campaigns_time)::timestamp) as campaigns_expiry_uts 
                            from campaigns 
                            inner join product on product.product_id = campaigns.product_id	
                            left join product_attribute on  product_attribute.product_id = product.product_id
                            left join favourate on favourate.product_id = product.product_id 
                                        and favourate.user_id = '{$user_id}' where campaigns_id = {$campaigns_id}");
    }

    public static function getTicketsByOrderProductId($ticketId, $productId, $lang) {
        return DB::table('product_order_ticket_number')
            ->select('product.product_name', 'campaigns.campaigns_title', 'campaigns.campaigns_image', 'campaigns.campaigns_image2', 'product_order_details.order_placed_date')
            ->join('product_order_history', 'product_order_ticket_number.history_id', '=', 'product_order_history.history_id')
            ->join('product', 'product_order_history.product_id', '=', 'product.product_id')
            ->join('product_order_details', 'product_order_history.order_block_id', '=', 'product_order_details.order_block_id')
            ->join('campaigns', 'campaigns.product_id', '=', 'product.product_id')
            ->where('product_order_ticket_number.ticket_number', $ticketId)
            ->first();
    }

    public static function getTicketsByOrderProductAll($user_id, $lang_code = "1", $filter = "", $sort = "")
    {
        return DB::select("SELECT 
        campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product_attribute.image as product_image 
        FROM product_order_ticket_number 
        join product_order_history on product_order_ticket_number.order_block_id=product_order_history.order_block_id
        and product_order_ticket_number.product_id=product_order_history.product_id  and product_order_ticket_number.product_attribute_id=product_order_history.product_attribute_id 
        join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status=0
        join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
        join product_attribute on product_attribute.product_id = product_order_history.product_id and product_attribute.product_attribute_id=
        product_order_history.product_attribute_id
        join product on product.product_id = product_order_history.product_id	
        where product_order_history.user_id='{$user_id}' and product_order_details.delivery_status<>3  
        group by campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,	
        product_attribute.image 
        order by(product_order_details.order_placed_date::timestamp)desc");
    }

    public static function getTicketsByOrderProductCategory($user_id, $category_id, $lang_code = "1", $filter = "", $sort = "")
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
                                    product_order_details.used_points

                                from (select product_id, max(history_id) as history_id from product_order_history where user_id={$user_id} group by product_id	) tickets 
                                left join product_order_history on product_order_history.history_id = tickets.history_id
                                left join product_order_details on product_order_details.order_block_id = product_order_history.order_block_id
                                inner join product on product.product_id = product_order_history.product_id	
                                inner join campaigns on campaigns.product_id = product_order_history.product_id	
                                inner join product_attribute on product_attribute.product_id = product_order_history.product_id
                                left join product_category on product_category.product_id = product.product_id
                                where 1=1 and campaigns.campaigns_status=0 and product_category.category_id={$category_id} 
                                order by product_order_details.product_order_id");
    }

    public static function getTicketsByOrderProductCategoryAll($user_id, $category_id, $lang_code = "1", $filter = "", $sort = "")
    {
        return DB::select("SELECT 
        campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,		
        product_attribute.image as product_image
        FROM product_order_ticket_number 
        join product_order_history on product_order_ticket_number.order_block_id=product_order_history.order_block_id
        join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status=0
        join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
        join product_attribute on product_attribute.product_id = product_order_history.product_id
        join product on product.product_id = product_order_history.product_id
        join product_category on product_category.product_id = product.product_id	
        where product_category.category_id={$category_id}  and product_order_history.user_id='{$user_id}' and product_order_details.delivery_status<>3  group by campaigns.campaigns_id,
        campaigns.campaigns_date,
        campaigns.campaigns_time,
        campaigns.campaigns_title,
        campaigns.campaigns_image,
        campaigns.campaigns_image2,
        product_order_details.order_placed_date,
        product_order_ticket_number.ticket_number ,
        product.product_name,
        product.product_id,
        product_attribute.product_attribute_id,		
        product_attribute.image order by(product_order_details.order_placed_date::timestamp)desc");
    }

    public static function getTicketCount($drawSlipNumber)
    {
        return DB::table('draw_slip')
            ->where('draw_slip_number', $drawSlipNumber)
            ->count();
    }

    public static function getWinnerList($user_id, $lang_code = "1", $filter = "", $sort = "", $country_id = 0)
    {
        return DB::select("SELECT 
                            campaigns.campaigns_id,
                            campaigns.campaigns_date,
                            campaigns.campaigns_draw_date,
                            campaigns.draw_date_manual,
                            campaigns.campaigns_time,
                            campaigns.campaigns_title,
                            campaigns.campaigns_image,
                            campaigns.campaigns_image2,
                            product_order_details.order_placed_date,
                            draw_slip.draw_slip_number,
                            product.product_name,
                            product.product_id,
                            product_attribute.product_attribute_id,	
                            product.product_image,
                            user_table.user_first_name, 
                            user_table.user_last_name,
                            user_table.image,
                            user_table.user_id
                            FROM draw_slip 
                            join product_order_history on draw_slip.order_block_id=product_order_history.order_block_id
                            join campaigns on product_order_history.product_id=campaigns.product_id and campaigns.campaigns_status<>0
                            join product_order_details on product_order_history.order_block_id=product_order_details.order_block_id
                            left join product_attribute on product_attribute.product_id = product_order_history.product_id and product_attribute.product_attribute_id=
                            (select    min(product_attribute.product_attribute_id)  from product_attribute  where product_attribute.product_id = product_order_history.product_id) 
                            join product on product.product_id = product_order_history.product_id
                            join user_table on user_table.user_id = product_order_history.user_id	
                            where product_order_history.user_id=user_table.user_id and draw_slip.draw_slip_status=1  and user_table.user_country_id={$country_id}  
                            group by campaigns.campaigns_id,user_table.user_first_name,user_table.user_last_name,
                            user_table.image,
                            campaigns.campaigns_date,
                            campaigns.campaigns_draw_date,
                            campaigns.draw_date_manual,
                            campaigns.campaigns_time,
                            campaigns.campaigns_title,
                            campaigns.campaigns_image,
                            campaigns.campaigns_image2,
                            product_order_details.order_placed_date,
                            draw_slip.draw_slip_number ,
                            product.product_name,
                            product.product_id,
                            product.product_image,
                            product_attribute.product_attribute_id,	
                            product_attribute.image,user_table.user_id order by(product_order_details.order_placed_date::timestamp)desc");
    }
}
