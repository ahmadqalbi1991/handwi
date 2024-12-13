<?php

namespace App\Http\Controllers\admin;

use App\Models\ContactUsModel;
use App\Models\Rating;
use App\Models\ProductModel;
use App\Models\VendorDetailsModel;
use App\Models\Service;
use App\Models\RatingReply;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        $page_heading = "Rating Controller";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        $list = Rating::select('ratings.*','ratings.id as id','ratings.created_at','users.name')->join('users','users.id','=','ratings.user_id')->orderBy('ratings.created_at','desc')->paginate(10);
        
        foreach ($list as $key => $value) {
            $list[$key]->reply = RatingReply::where('rating_id',$value->id)->get()->count();
            $list[$key]->name_product = "";
            if($value->type == 1)
            {
            $product = ProductModel::where('id',$value->product_id)->first();
            $list[$key]->name_product = !empty($product->product_name) ? $product->product_name: '';    
            }
            if($value->type == 2)
            {
            $vendor = VendorDetailsModel::where('user_id',$value->vendor_id)->first();
            $list[$key]->name_product = !empty($vendor->company_name) ? $vendor->company_name: ''; 
            }
            if($value->type == 3)
            {
                $service = Service::find($value->service_id);
                if($service) {
                    $service = $service->first();
                    $list[$key]->name_product = !empty($service->name) ? $service->name: ''; 
                }
            }
        }
        return view("admin.rating.list", compact("page_heading", "list", "search_key"));
    }

    public function delete($id = '')
    {
        // if (!have_prmission('CNTCT_US', 'D')) {
        //     $status = "0";
        //     $message = "You don't have permission to do this action";
        //     echo json_encode(['status' => $status, 'message' => $message]);die();
        // }
        Rating::where('id', $id)->delete();
        $status = "1";
        $message = "details removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function reply_comment(Request $request)
    {
        $datains = new RatingReply;
        $datains->rating_id = $request->commentid;
        $datains->message = $request->reply;
        $datains->user_id = Auth::user()->id;
        $datains->created_at = gmdate('Y-m-d H:i:s');
        $datains->updated_at = gmdate('Y-m-d H:i:s');
        $datains->save();
       
        $status = "1";
        $message = "Replied successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function viewReply($id)
    {
       
        $page_heading = "Rating reply";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        
        
        $list = RatingReply::select('*','ratings_reply.id as id','ratings_reply.created_at as created_at')->join('users','users.id','=','ratings_reply.user_id');
        
        
        $list = $list->orderBy('ratings_reply.created_at','desc')->where('rating_id',$id)->paginate(10);

        foreach ($list as $key => $value) {
            $list[$key]->name_product = "";
            if($value->type == 1)
            {
            $product = ProductModel::where('id',$value->product_id)->first();
            $list[$key]->name_product = !empty($product->product_name) ? $product->product_name: '';    
            }
            if($value->type == 2)
            {
            $vendor = VendorDetailsModel::where('user_id',$value->vendor_id)->first();
            $list[$key]->name_product = !empty($vendor->company_name) ? $vendor->company_name: ''; 
            }
            if($value->type == 3)
            {
                $service = Service::find($value->service_id);
                if($service) {
                    $service = $service->first();
                    $list[$key]->name_product = !empty($service->name) ? $service->name: ''; 
                }
            }
        }
        return view("admin.rating.reply", compact("page_heading", "list", "search_key"));
    }
    public function deleteReply($id = '')
    {
        RatingReply::where('id', $id)->delete();
        $status = "1";
        $message = "details removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    

}
