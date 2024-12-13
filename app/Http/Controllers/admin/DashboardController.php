<?php

namespace App\Http\Controllers\Admin;
use App\Models\CampaignModel;
use App\Models\Product;
use App\Models\TicketModel;
use App\Models\UserTable;
use Carbon\Carbon;
use DB;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['page_heading'] = 'Dashboard';
        $data['total_customers'] = UserTable::where(['mazouz_customer' => 1, 'user_status' => 1])->count();
        $total_active_campaigns = Product::getAllProducts(108, 0, $lang_code = "1", $filter = "", $sort = "order by product_id desc");
        $winner_list = TicketModel::getWinnerList(0, 1, "", "", 108);
        $data['active_campaigns'] = $total_active_campaigns;
        $data['total_winners'] = count($winner_list);
        $campaigns_list = $total_orders = [];
        foreach ($total_active_campaigns as $campaign) {
            $total_recs = DB::table('product_order_details as pod')
                ->join('product_order_history as poh', 'poh.order_block_id', 'pod.order_block_id')
                ->where('poh.product_id', $campaign->product_id)
                ->whereDate('pod.order_placed_date', Carbon::now()->format('Y-m-d'))
                ->count();

            $total_orders[] = $total_recs;
            $campaigns_list[] = $campaign->campaigns_title;
        }

        $data['campaigns_list'] = $campaigns_list;
        $data['total_orders'] = $total_orders;

        return view('admin.dashboard')->with($data);
    }

}
