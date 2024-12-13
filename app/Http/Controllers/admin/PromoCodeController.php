<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignModel;
use App\Models\CountriesModel;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\PromoCodeCampaign;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data['page_heading'] = 'Promo Codes';
            $data['promo_codes'] = PromoCode::with('campaigns')->get();

            return view('admin.promo-codes.index')->with($data);
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = PromoCode::find($request->id);
            if ($campaign) {
                $campaign->is_active = $request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'Promo Code not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function create($id = null)
    {
        try {
            $data['page_heading'] = 'Promo Code Create';
            $products_data = Product::getAllProducts(108, 0, $lang_code = "1", $filter = "", $sort = "order by product_id desc");
            $data['campaigns'] = $products_data;
            if ($id) {
                $data['promo_code'] = PromoCode::with('campaigns')->where('id', $id)->first();
            }

            return view('admin.promo-codes.create')->with($data);
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function delete($id)
    {
        try {
            $promo_code = PromoCode::where('id', $id)->first();
            $promo_code->campaigns()->delete();
            $promo_code->delete();

            return return_response('1', 200, 'Promo code deleted');
        } catch (\Exception $exception) {
            dd($exception);
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function save(Request $request) {
        try {
            $input = $request->except('_token');
            $campaigns_id = [];
            if (!empty($input['campaigns_id'])) {
                $campaigns_id = $input['campaigns_id'];
                unset($input['campaigns_id']);
            }

            if (empty($input['id'])) {
                $input['is_active'] = 1;
                $promo_code = PromoCode::create($input);
            } else {
                PromoCode::where('id', $input['id'])->update($input);
                $promo_code = PromoCode::find($input['id']);
            }

            if (count($campaigns_id)) {
                foreach ($campaigns_id as $id) {
                    PromoCodeCampaign::create([
                        'promo_code_id' => $promo_code->id,
                        'campaign_id' => $id
                    ]);
                }
            }

            return return_response('1', 200, 'Promo code saved successfully');
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }
}
