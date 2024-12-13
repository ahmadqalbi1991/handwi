<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignModel extends Model
{
    use HasFactory;
    protected $table = 'campaigns';
    public $timestamps = false;
    protected $primaryKey = 'campaigns_id';
    protected $fillable = [
        'campaigns_title',
        'campaigns_date',
        'campaigns_time',
        'campaigns_qty',
        'campaigns_desc',
        'product_id',
        'country_id',
        'campaigns_status',
        'campaigns_date_start',
        'campaigns_time_start',
        'campaigns_draw_date',
        'schedule_now',
        'is_featured',
        'campaigns_image2',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function product_attribute() {
        return $this->belongsTo(ProductAttribute::class, 'product_id');
    }

    public static function getTicketsForCampaign($campaign_id, $where = '') {
        return DB::select("select DISTINCT ON (draw_slip_number) draw_slip_number,
                            campaigns_id,
                            campaigns_title,
                            draw_slip.order_block_id,
                            product_order_ticket_number.product_attribute_id,
                            campaigns_image,
                            order_placed_date,
                            user_table.user_first_name,
                            user_table.user_last_name
                        from draw_slip
                        left join campaigns on campaigns.campaigns_id = draw_slip.campaign_id 
                        left join product_order_details on product_order_details.order_block_id = draw_slip.order_block_id
                                            left join product_order_ticket_number on product_order_ticket_number.order_block_id = product_order_details.order_block_id
                                            left join user_table on product_order_details.user_id = user_table.user_id
                        where draw_slip.campaign_id	={$campaign_id} {$where}");
    }

    public static function getDrawSlips($where)
    {
        return DB::table('draw_slip')
            ->select('draw_slip_number')
            ->where($where)
            ->get()
            ->toArray();
    }

    public static function getCampaignReport($data)
    {
        $search_key  = strtolower($data['search_key']);
        $sql = " select  distinct on (p.product_id) p.product_id,draw_slip_number,campaigns_status,campaigns_id,product_name,campaigns_title,campaigns_title_arabic,campaigns_date,campaigns_time,campaigns_date_start,campaigns_time_start,campaigns_qty,campaigns_desc,campaigns_desc_arabic,campaigns_image,
             (select count(order_block_id) from product_order_ticket_number pt where pt.product_id=p.product_id) as sold_ticket
            from product p,campaigns c LEFT JOIN draw_slip ds ON ds.campaign_id=c.campaigns_id and draw_slip_status=1 where p.product_id=c.product_id ";
        $country_id = (int) $data['country'];

        if($country_id > 0) {
            $sql.= " and c.country_id = ". $country_id;
        }

        if($search_key!="" && $search_key!=NULL)
        {
            $sql.= " and (  LOWER(product_name) like '%".$search_key."%'  or LOWER(campaigns_title_arabic) like '%".$search_key."%' or  LOWER(campaigns_title) like '%".$search_key."%' or  LOWER(campaigns_title) like '%".$search_key."%'  ) ";
        }

        if(isset($data['status']) && $data['status']>=0)
        {
            $sql.= "     and campaigns_status=".$data['status']." ";
        }
        if(isset($data['txt_sale_datefrom']) && $data['txt_sale_datefrom']!="" && isset($data['txt_sale_dateto']) && $data['txt_sale_dateto']!="")
        {
            $sql.= "     and date(campaigns_date)<='".$data['txt_sale_dateto']."' and date(campaigns_date)>='".$data['txt_sale_datefrom']."' ";
        }

        $sql.= "     and p.product_status=1 ";


        $sql.= "      order by product_id DESC  ";

        return DB::select($sql);
    }
}
