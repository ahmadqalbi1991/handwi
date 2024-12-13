<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CampaignModel;
use App\Models\City;
use App\Models\CMS;
use App\Models\ContactUsModel;
use App\Models\CountriesModel;
use App\Models\CountryModel;
use App\Models\Product;
use App\Models\SpinnerModel;
use App\Models\UserTable;
use App\Models\UserTemp;
use App\Services\JWTService;
use App\Traits\SMSTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Kreait\Firebase\ServiceAccount;

class HomeController extends Controller
{
    use SMSTrait;
    public function CMSContent(Request $request)
    {
        try {
            $content = CMS::where('slug', $request->type)->first();
            if ($content) {
                $content = convertNumbersToStrings($content->toArray());
            } else {
                $content = (object)$content;
            }
            $o_data['article'] = $content;

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function prizeList()
    {
        try {
            $prize_list = SpinnerModel::orderBy('spinner_price_id', 'asc')->get();
            $o_data['prize_list'] = convertNumbersToStrings($prize_list->toArray());

            return return_response('1', 200,'', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getCountries(Request $request) {
        try {
            $langCode = $request->language ? $request->language : 1;
            $countries = CountriesModel::select('countries_id as country_id', 'countries_nice_name as country_name', 'countries_phonecode as dial_code')
                ->get();

            foreach ($countries as $country) {
                $country->flag = url('uploads/flags/' . $country->dial_code . '.png');
            }

            return return_response('1', 200, '', [], $countries);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function submitContactUs(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string',
                'email' => 'email|required',
                'user_message' => 'required'
            ], [
                'full_name.required' => __('messages.validation.required', ['field' => __('messages.common_messages.full_name')]),
                'email.required' => __('messages.validation.required', ['field' => __('messages.common_messages.email')]),
                'email.email' => __('messages.validation.email'),
                'user_message.required' => __('messages.validation.required', ['field' => __('messages.common_messages.message')]),
            ]);

            if ($validator->fails()) {
                return return_response('0', 200, '', $validator->errors());
            }

            $contact_us = ContactUsModel::create([
                'name' => $request->full_name,
                'email' => $request->email,
                'message' => $request->user_message,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

            if ($contact_us) {
                $status = '1';
                $message = __('messages.success.got_response');
                $code = 200;
            } else {
                $status = '0';
                $message = __('messages.errors.something_went_wrong');
                $code = 500;
            }

            return return_response($status, $code, $message);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function getCities(Request $request) {
        try {
            $cities = City::select('city_id', 'city_name')->where(['city_language_code' => $request->language, 'city_country_id' => $request->country_id, 'city_status' => 1])->get();
            $cities = convertNumbersToStrings($cities->toArray());
            $cities[] = (object) ['city_id' => '-1','city_name'=>'Other'];
            return return_response('1', 200, '', [], $cities);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function faqs(Request $request) {
        try {
            $language = $request->has('language') ? $request->language : 1;
            $country_id = $request->has('country_id') ? $request->country_id : 1;
            $o_data['article'] = Product::getFaq($country_id, $language);

            return return_response('1', 200, '', [], $o_data);
        } catch (\Exception $exception) {
            return return_response('0', 500, __('messages.errors.something_went_wrong'));
        }
    }

    public function testSMS(Request $request){
        $serviceAccount = ServiceAccount::fromJsonFile(asset('firebase-ma7zouz.json'));
        dd($serviceAccount);
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();

        // Get a reference to the Firebase Realtime Database
        $database = $firebase->getDatabase();

        // Push the user data to the "DGUsers" node in Firebase
        $fbUserReference = $database->getReference('DGUsers/')
            ->push([
                "fcm_token" => trim($request->input('user_device_token')),
                "user_name" => $request->input('user_first_name') . " " . $request->input('user_last_name'),
            ]);
    }
}
