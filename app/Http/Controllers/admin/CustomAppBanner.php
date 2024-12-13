<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignModel;
use App\Models\Product;
use Illuminate\Http\Request;
use Validator;
use App\Models\CustomBanner;
use App\Models\ProductModel;
use App\Models\Service;
use App\Models\Categories;
use App\Models\ActivityType;
use App\Models\VendorModel;
use App\Models\AppHomeSection;
use DB;

class CustomAppBanner extends Controller
{
    //
    public function index()
    {
        $page_heading = "Custom Banners";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $params['banner_type'] = $_GET['banner_type'] ?? '';
        $bannertype = $params['banner_type'];
        $search_key = $params['search_key'];

        $list = CustomBanner::get();

        return view("admin.custom_banner.list", compact("page_heading", "list", "search_key", 'bannertype'));
    }

    public function create($banner_id = null)
    {
        $products = CampaignModel::whereHas('product')->where('campaigns_status', 1)->get();
        $banner = null;
        if ($banner_id) {
            $banner = CustomBanner::find($banner_id);
        }

        $page_heading = "Create App Banner";
        return view('admin.custom_banner.create', compact('page_heading', 'products', 'banner'));
    }

    public function save(Request $request) {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'bi_name' => 'required',
                'bi_status' => 'required',
                'product_id' => 'required',
            ], [
                'bi_name.required' => 'Banner is required',
                'bi_status.required' => 'Status is required',
                'bi_product_id.required' => 'Product id is required',
            ]);

            if ($validator->fails()) {
                return return_response('2', 200, '', $validator->errors());
            }

            $input = $request->all();

            if ($request->hasFile('bi_image')) {
                $file = $request->file('bi_image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $s3Path = 'banners/' . $fileName;
                $filePath = \Storage::disk('s3')->put($s3Path, file_get_contents($file));
                $input['bi_image'] = \Storage::disk('s3')->url($s3Path);
            }

            $product_attr_id = DB::table('product_attribute')->select('product_attribute_id')->first();
            $input['product_attr_id'] = $product_attr_id->product_attribute_id;

            if (!$request->has('id')) {
                $banner = CustomBanner::create($input);
            } else {
                $banner = CustomBanner::where('id', $input['id'])->update($input);
            }

            if ($banner) {
                return  return_response('1', 200, 'Banner saved successfully');
            } else {
                return  return_response('0', 500, 'Something went wrong');
            }
        } catch (\Exception $exception) {
            return  return_response('0', 500, 'Something went wrong');
        }
    }

    public function delete($id)
    {
        try {
            CustomBanner::where('id', $id)->delete();

            return return_response('1', 200, 'Banner deleted');
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = CustomBanner::find($request->id);
            if ($campaign) {
                $campaign->bi_status = $request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'Banner not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }
}
