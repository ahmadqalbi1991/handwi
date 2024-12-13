<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\BuildingTypes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceCategories;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Rating;
use App\Models\ServiceInclude;
use App\Models\HourlyRate;
use App\Models\VendorModel;
use App\Models\VendorDetailsModel;
use App\Models\BannerModel;
use App\Models\ActivityType;
use App\Models\ServiceCategorySelected;
use App\Models\AppHomeSection;
use App\Models\CustomBanner;
use Validator;

class ServiceController extends Controller
{

    public function service_type(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $serviceTypes = BuildingTypes::where(['active' => 1, 'deleted' => 0])->get();

        $o_data['list'] = convert_all_elements_to_string($serviceTypes);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }

    public function service_categories(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'parent_id'=>0,'activity_id'=>6])->orderBy('sort_order','asc')
        ->get();
        $categorieslist = [];
        $key = 0;
        foreach ($categories as $value) {
            $count = ServiceCategories::join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            ->join('service','service.id','=','service_category_selected.service_id')
            //->join('service_price','service_price.service_id','=','service.id')
            ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            ->where(['service.deleted'=>0,'service.active'=>1])
            //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            ->get()->count();
            
            if($count > 0)
            {
              $categorieslist[$key] = new \stdClass;
              $categorieslist[$key]->id = $value->id;
              $categorieslist[$key]->name = $value->name;
              $categorieslist[$key]->image = $value->image;
              $categorieslist[$key]->description = (string) $value->description; 
            //   $categorieslist[$key]->sub_categories = ServiceCategories::select('service_category.id','service_category.name','service_category.image')
            //                                         ->join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            //                                         ->join('service','service.id','=','service_category_selected.service_id')
            //                                         //->join('service_price','service_price.service_id','=','service.id')
            //                                         ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            //                                         ->where(['service.deleted'=>0,'service.active'=>1])
            //                                         //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            //                                         ->where(['service_category.active'=>1,'service_category.deleted'=>0,'parent_id'=>$value->id])
            //                                         ->distinct('id')
            //                                         ->orderBy('id')
            //                                         ->orderBy('service_category.sort_order','asc')
            //   ->get(); 
            $categorieslist[$key]->sub_categories = ServiceCategories::select('service_category.id','service_category.name','service_category.image')
                                                    ->whereRaw("service_category.id in (select category_id from service_category_selected scs join service s on scs.service_id=s.id where s.deleted=0 and s.active=1 and s.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) )")
                                                    
                                                    ->where(['service_category.active'=>1,'service_category.deleted'=>0,'parent_id'=>$value->id])
                                                    ->orderBy('service_category.sort_order','asc')
              ->get(); 
              $key++;
            }
            
        }

        $activity  = ActivityType::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->select('id','name','description','logo','banner_image','indvidual_logo as all_image')
        ->find(6);
        if($activity){
            $o_data['activity'] = convert_all_elements_to_string($activity);
        }
        $o_data['list'] = convert_all_elements_to_string($categorieslist);

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function service_categories_health(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'parent_id'=>0,'activity_id'=>4])->orderBy('sort_order','asc')
        ->get();
        $categorieslist = [];
        $key = 0;
        foreach ($categories as $value) {
            $count = ServiceCategories::join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            ->join('service','service.id','=','service_category_selected.service_id')
            //->join('service_price','service_price.service_id','=','service.id')
            ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            ->where(['service.deleted'=>0,'service.active'=>1])
            //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            ->get()->count();
            
            if($count > 0)
            {
              $categorieslist[$key] = new \stdClass;
              $categorieslist[$key]->id = $value->id;
              $categorieslist[$key]->name = $value->name;
              $categorieslist[$key]->image = $value->image;
              $categorieslist[$key]->description = (string) $value->description; 
            //   $categorieslist[$key]->sub_categories = ServiceCategories::select('service_category.id','service_category.name','service_category.image')
            //                                         ->join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            //                                         ->join('service','service.id','=','service_category_selected.service_id')
            //                                         //->join('service_price','service_price.service_id','=','service.id')
            //                                         ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            //                                         ->where(['service.deleted'=>0,'service.active'=>1])
            //                                         //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            //                                         ->where(['service_category.active'=>1,'service_category.deleted'=>0,'parent_id'=>$value->id])
            //                                         ->distinct('id')
            //   ->get(); 
            $categorieslist[$key]->sub_categories = ServiceCategories::select('service_category.id','service_category.name','service_category.image')
                                                    ->whereRaw("service_category.id in (select category_id from service_category_selected scs join service s on scs.service_id=s.id where s.deleted=0 and s.active=1 and s.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) )")
                                                    
                                                    ->where(['service_category.active'=>1,'service_category.deleted'=>0,'parent_id'=>$value->id])
                                                    ->orderBy('service_category.sort_order','asc')
              ->get(); 
              $key++;
            }
            
        }

        $o_data['list'] = convert_all_elements_to_string($categorieslist);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function service_categories_transport(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'parent_id'=>0,'activity_id'=>1])->orderBy('sort_order','asc')
        ->get();
        $categorieslist = [];
        $key = 0;
        foreach ($categories as $value) {
            $count = ServiceCategories::join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            ->join('service','service.id','=','service_category_selected.service_id')
            //->join('service_price','service_price.service_id','=','service.id')
            ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            ->where(['service.deleted'=>0,'service.active'=>1])
            //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            ->get()->count();
            
            if($count > 0)
            {
              $categorieslist[$key] = new \stdClass;
              $categorieslist[$key]->id = $value->id;
              $categorieslist[$key]->name = $value->name;
              $categorieslist[$key]->image = $value->image;
              $categorieslist[$key]->description = (string) $value->description; 
              $categorieslist[$key]->sub_categories = ServiceCategories::select('service_category.id','service_category.name','service_category.image')
                                                    ->join('service_category_selected','service_category_selected.category_id','=','service_category.id')
                                                    ->join('service','service.id','=','service_category_selected.service_id')
                                                    //->join('service_price','service_price.service_id','=','service.id')
                                                    ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
                                                    ->where(['service.deleted'=>0,'service.active'=>1])
                                                    //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
                                                    ->where(['service_category.active'=>1,'service_category.deleted'=>0,'parent_id'=>$value->id])
                                                    ->distinct('id')
              ->get(); 
              $key++;
            }
            
        }

        $o_data['list'] = convert_all_elements_to_string($categorieslist);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function service_categories_details(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $services_id = [];
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $page= $request->page??1;
        $limit = $request->limit??10;
        $offset = ($page - 1) * $limit;
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'id'=>$request->category_id])->first();
        
        $sub_categories = ServiceCategories::select('id')->where(['active'=>1,'deleted'=>0,'parent_id'=>$request->category_id])->get()->toArray();
        $sub_categories[] = $request->category_id;
        
        if(!empty($categories))
        {
            $o_data = convert_all_elements_to_string($categories->toArray());
        }else{
            $activity = ActivityType::find($request->activity_id);
            $o_data = [
                "id"=>"",
                "name"=>"All",
                "image"=>"",
                "banner_image"=>$activity->banner_image,
                "parent_id"=>"",
                "active"=>"",
                "deleted"=>"",
                "order"=> "",
                "description"=>"",
                "created_at"=>"",
                "updated_at"=>"",
                "activity_id"=>"",
                "sort_order"=> ""
            ];
        }
            $services = Service::select('service.*','service_category_selected.*')->where(['service.active'=>1,'service.deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')
        ->join('service_category','service_category.id','=','service_category_selected.category_id')->orderBy('service.sort_order','asc');

        //->join('service_price','service_price.service_id','=','service.id')
        if($request->category_id != ''){
            $services=$services->whereIn('service_category_selected.category_id',$sub_categories);
        }
        if($request->activity_id != ''){
            $services=$services->where('service_category.activity_id',$request->activity_id);
        }
        
        if(!empty($request->search))
        {

        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->distinct('service_category_selected.service_id','service.sort_order')->skip($offset)->take($limit)
        ->get();
        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
             $parent_id = ServiceCategorySelected::where('service_id',$value->service_id)
            ->leftjoin('service_category','service_category.id','service_category_selected.category_id')->first()->parent_id??0;
            $services[$key]->subcategory_id = $parent_id == 0 ? 0 : 1;
           
            $services[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            
            $services[$key]->hourly_rate = $hourly_rate;
        }

        $subcategories = ServiceCategories::where(['active'=>1,'deleted'=>0,'parent_id'=>$request->category_id])->orderBy('sort_order','asc')->get();
        $subcategorieslist = [];
        $key = 0;
        foreach ($subcategories as $value) {
            
            $count = ServiceCategorySelected::select('service_id')
            ->where('service_category_selected.category_id',$value->id)->pluck('service_id')->toArray();

            $count = Service::whereIn('id',$count)->where('service.active',1)->get()->count();
            if($count > 0)
            {
              $subcategorieslist[$key] = new \stdClass;
              $subcategorieslist[$key]->id = $value->id;
              $subcategorieslist[$key]->name = $value->name;
              $subcategorieslist[$key]->image = $value->image;
              $subcategorieslist[$key]->description = (string) $value->description;  
              $key++;
            }
            
          }
          foreach ($services as $key => $value_services) {
            $services_id[] = $value_services->id;
          }

            $o_data['sub_categories'] = convert_all_elements_to_string($subcategorieslist);
            $o_data['service_list'] = convert_all_elements_to_string($services);
            $where = $services_id;
            $ratingdata = Rating::rating_list_by_services($services_id);
            $ratingavg = Rating::avg_rating_wherein($services_id);
            $o_data['avg_rating'] = (string) $ratingavg;
            $o_data['rating'] = convert_all_elements_to_string($ratingdata);
        
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function service_home_load(REQUEST $request){
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
        $page =$request->page??1;
        $limit=$request->limit??10;
        $offset=($page - 1) * $limit;
         $type = [1,2,4];
         $activity = [0,7,6];
        
        //dynamic section start
        $dynamic_contents = [];
        $list = AppHomeSection::where(['status'=>1])->orderBy('sort_order','asc')->take($limit)->skip($offset)->get();
        foreach($list as $k=> $key){
            if($key->type=='popular_services'){
                $subcategories = ServiceCategories::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->limit(10)->get();
                $subcategorieslist = [];
                $key1 = 0;
                foreach ($subcategories as $value) {
                    
                    $count = ServiceCategorySelected::select('service_id')
                    ->where('service_category_selected.category_id',$value->id)->pluck('service_id')->toArray();
        
                    $count = Service::whereIn('id',$count)->where('service.active',1)->orderBy('service.sort_order','asc')->get()->count();
                    if($count > 0)
                    {
                      $subcategorieslist[$key1] = new \stdClass;
                      $subcategorieslist[$key1]->id = $value->id;
                      $subcategorieslist[$key1]->name = $value->name;
                      $subcategorieslist[$key1]->image = $value->image;
                      $subcategorieslist[$key1]->description = (string) $value->description;  
                      $key1++;
                    }
                    
                  }
                  if($subcategories->count() > 0){
                      $ins=$key;
                      $key->content=$list[$k]['content'] = $subcategorieslist;;
                      $dynamic_contents[] = $ins;
                  }
                  
            }
            if($key->type=='new_offer'){
                $banner1  = BannerModel::where(['active'=>1,'banner_type'=>2])->whereIn('type',$type)->limit(10)->get();
                foreach ($banner1 as $keyIndex => $val) {
                  $banner1[$keyIndex]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
                }
                $list[$k]['content'] = $banner1->toArray();
            }
            if($key->type=='most_booked_services'){
                //most booked
                $services_booked = Service::select('service.*','service.id as service_id','service.id as category_id')
                ->where(['service.active'=>1,'service.deleted'=>0,'service_category.activity_id'=>6])
                ->join('service_category_selected','service_category_selected.service_id','=','service.id')
                ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
                ->where('order_count','>',0)->limit(10);
                if(!empty($request->search))
                {
        
                    $services_booked =$services_booked->where('service.name', 'ilike', '%'.$request->search.'%');
                }
                $services_booked = $services_booked->orderBy('order_count','desc')
                ->get();
                
                foreach ($services_booked as $keyIndex => $value) {
                    $services_booked[$keyIndex]->regular_price = (string) 0;
                    $services_booked[$keyIndex]->description = (string) $value->description;
                    $where['service_id']   = $value->id;
                    $services_booked[$keyIndex]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
                    $services_booked[$keyIndex]->rating_count = Rating::where($where)->get()->count();
                    $ratingdata = Rating::rating_list($where);
                   
                    $services_booked[$keyIndex]->rating_details = $ratingdata;
                    $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->id])->get();
                    $services_booked[$keyIndex]->hourly_rate = $hourly_rate;
                }
                $list[$k]['content'] = $services_booked->toArray();
                //most booked
            }
            if($key->type=='new_services'){
                 $services = Service::select('service.*','service_category_selected.*')->where(['service.active'=>1,'service.deleted'=>0,'service_category.activity_id'=>6])
                    ->join('service_category_selected','service_category_selected.service_id','=','service.id')
                    ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
                    ->orderBy('service.id','desc')->limit(10);
                    
                    if(!empty($request->search))
                    {
            
                        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
                    }
                    $services = $services->distinct('service.id')
                    ->get();
                    foreach ($services as $keyIndex => $value) {
                        $services[$keyIndex]->regular_price = (string) 0;
                        $services[$keyIndex]->description = (string) $value->description;
                        $where['service_id']   = $value->service_id;
                        $services[$keyIndex]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
                        $services[$keyIndex]->rating_count = Rating::where($where)->get()->count();
                        $ratingdata = Rating::rating_list($where);
                       
                        $services[$keyIndex]->rating_details = $ratingdata;
                        
                        $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
                        $services[$keyIndex]->hourly_rate = $hourly_rate;
                    }
                    $list[$k]['content'] = $services->toArray();
            }
            if($key->type=='custom_banner'){
                $banner_list = CustomBanner::where(['id'=>$key->entity_id])->get();
                if($banner_list->count()>0){
                    $list[$k]['content'] = $banner_list->toArray();
                }else{
                   $list[$k]['content'] = [];
                }
            }
            if($key->type=='category'){
                $services = Service::select('service.*','service_category_selected.*')->where(['active'=>1,'deleted'=>0])
                ->join('service_category_selected','service_category_selected.service_id','=','service.id')->limit(10)
                ->where('service_category_selected.category_id',$key->entity_id);
                if(!empty($request->search))
                {
                $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
                }
                $services = $services->distinct('service_category_selected.service_id','sort_order')->orderBy('sort_order','asc')
                ->get();
        
                foreach ($services as $keyIndex => $value) {
                    $services[$keyIndex]->regular_price = (string) 0;
                    $services[$keyIndex]->description = (string) $value->description;
                    $where['service_id']   = $value->service_id;
                    $services[$keyIndex]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
                    $services[$keyIndex]->rating_count = Rating::where($where)->get()->count();
                    $ratingdata = Rating::rating_list($where);
                   
                    $services[$keyIndex]->rating_details = $ratingdata;
                    $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
                    $services[$keyIndex]->hourly_rate = $hourly_rate;
                }
                if($services->count()>0){
                    $list[$k]['content'] = $services->toArray();
                }else{
                   $list[$k]['content'] = [];
                }
            }
            
        }
        $o_data['dynamic_list'] = array_values($list->toArray());
        //dynamic end
        $o_data = convert_all_elements_to_string2($o_data);

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => convert_all_elements_to_string($o_data)], 200);
    }
    public function service_home(REQUEST $request){
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => (string) 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
        //banner 
        $type = [1,2,4];
        $activity = [0,7,6];
        $banner  = BannerModel::where(['active' => 1,'banner_type' => 1])->whereIn('type',$type)->whereIn('activity',$activity)->get();
        foreach ($banner as $key => $val) {
            $banner[$key]->banner_image = url(config('global.upload_path') . config('global.banner_image_upload_dir') . $val->banner_image);
            unset($banner[$key]->active);
            unset($banner[$key]->created_by);
            unset($banner[$key]->updated_by);
            unset($banner[$key]->created_at);
            unset($banner[$key]->updated_at);
        }
        //banner END
        $activities  = ActivityType::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->select('id','name','description','logo')->get();
        
        
        
        $con = \App\Models\ContactUsSetting::first();

        $o_data['transport_website_link']  = $con->transport_website_link;

        $o_data['mobile']    = $con->mobile;
        $o_data['email']    = $con->email;
        $o_data['twitter']    = $con->twitter;
        $o_data['instagram']    = $con->instagram;
        $o_data['facebook']   = $con->facebook;
        $o_data['youtube']   = $con->youtube;
        $o_data['linkedin']   = $con->linkedin;
        


        $o_data['banner']  = $banner;
        $o_data['activities']  = $activities;
        
        $subcategories = ServiceCategories::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->limit(10)->get();
        $subcategorieslist = [];
        $key = 0;
        foreach ($subcategories as $value) {
            
            $count = ServiceCategorySelected::select('service_id')
            ->where('service_category_selected.category_id',$value->id)->pluck('service_id')->toArray();

            $count = Service::whereIn('id',$count)->where('service.active',1)->orderBy('service.sort_order','asc')->get()->count();
            if($count > 0)
            {
              $subcategorieslist[$key] = new \stdClass;
              $subcategorieslist[$key]->id = $value->id;
              $subcategorieslist[$key]->name = $value->name;
              $subcategorieslist[$key]->image = $value->image;
              $subcategorieslist[$key]->description = (string) $value->description;  
              $key++;
            }
            
          }
          $o_data['popular_categories'] = $subcategorieslist;
        
        $o_data = convert_all_elements_to_string($o_data);

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => convert_all_elements_to_string($o_data)], 200);
    }
    public function service_home_old(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = (object)[];
        $errors = [];
        $validator = Validator::make($request->all(), [
           
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
        $service_categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->orderBy('sort_order','asc')->get();

        
        $all_service_cat = [];
        $services = [];
        foreach ($service_categories as $key_main => $value) {

            $services = Service::select('service.*','service_category_selected.*')->where(['active'=>1,'deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')->limit(10)
        ->where('service_category_selected.category_id',$value->id);
        if(!empty($request->search))
        {
        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        $services = $services->distinct('service_category_selected.service_id')
        ->get();

        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            $services[$key]->hourly_rate = $hourly_rate;
            $service_categories[$key_main]['service_list'] = $services;
        }
        
        

          
        }
        foreach($service_categories as $service_cat)
        {
            if(!empty($service_cat->service_list) && count($service_cat->service_list) > 0)
            {
                $all_service_cat[] = $service_cat;
            }
        }
        
       
         
        $all_service_cat1 = [];
        $all_service_cat2 = [];
        $all_service_cat3 = [];
        $all_service_cat4 = [];
        $all_service_cat5 = [];
        $all_service_cat6 = [];
        $all_service_cat7 = [];
        foreach ($all_service_cat as $key => $value) {
            if($key <= 0)
            {
                $all_service_cat1[] =  $value; 
            }
            if($key > 0 && $key <= 1)
            {
                $all_service_cat2[] =  $value; 
            }
            if($key > 1 && $key <= 2)
            {
                $all_service_cat3[] =  $value; 
            }
            if($key > 2 && $key <= 3)
            {
                $all_service_cat4[] =  $value; 
            }
            if($key > 3 && $key <= 4)
            {
              
                $all_service_cat5[] =  $value; 
            }
            if($key > 4 && $key <= 5)
            {
                $all_service_cat6[] =  $value; 
            }
           

        }


        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->first();
        
        
        if(!empty($categories))
        {
            $o_data = convert_all_elements_to_string($categories->toArray());
            $services = Service::select('service.*','service_category_selected.*')->where(['service.active'=>1,'service.deleted'=>0,'service_category.activity_id'=>6])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')
        ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
        ->orderBy('service.id','asc')->limit(10);
        //->join('service_price','service_price.service_id','=','service.id')
        //->where('service_category_selected.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->distinct('service.id')
        ->get();
        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            $services[$key]->hourly_rate = $hourly_rate;
        }

        //most booked
        $services_booked = Service::select('service.*','service.id as service_id','service.id as category_id')
        ->where(['service.active'=>1,'service.deleted'=>0,'service_category.activity_id'=>6])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')
        ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
        ->where('order_count','>',0)->limit(10);
        //->join('service_price','service_price.service_id','=','service.id')
        //->where('service_category_selected.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $services_booked =$services_booked->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services_booked = $services_booked->orderBy('order_count','desc')
        ->get();
        
        foreach ($services_booked as $key => $value) {
            $services_booked[$key]->regular_price = (string) 0;
            $services_booked[$key]->description = (string) $value->description;
            $where['service_id']   = $value->id;
            $services_booked[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services_booked[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services_booked[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->id])->get();
            $services_booked[$key]->hourly_rate = $hourly_rate;
        }
        //most booked


        $subcategories = ServiceCategories::where(['active'=>1,'deleted'=>0,'activity_id'=>6])->limit(10)->get();
        $subcategorieslist = [];
        $key = 0;
        foreach ($subcategories as $value) {
            
            $count = ServiceCategorySelected::select('service_id')
            ->where('service_category_selected.category_id',$value->id)->pluck('service_id')->toArray();

            $count = Service::whereIn('id',$count)->where('service.active',1)->orderBy('service.sort_order','asc')->get()->count();
            if($count > 0)
            {
              $subcategorieslist[$key] = new \stdClass;
              $subcategorieslist[$key]->id = $value->id;
              $subcategorieslist[$key]->name = $value->name;
              $subcategorieslist[$key]->image = $value->image;
              $subcategorieslist[$key]->description = (string) $value->description;  
              $key++;
            }
            
          }
        $type = [3,4,1,2];
        $activity = [0,6,7];
        $banner  = BannerModel::where(['active'=>1,'banner_type'=>1])->whereIn('type',$type)->whereIn('activity',$activity)->get();
        foreach ($banner as $key => $val) {
           $banner[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        $banner1  = BannerModel::where(['active'=>1,'banner_type'=>2])->whereIn('type',$type)->whereIn('activity',$activity)->limit(10)->get();
        foreach ($banner1 as $key => $val) {
           $banner1[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        $banner2  = BannerModel::where(['active'=>1,'banner_type'=>3])->whereIn('type',$type)->whereIn('activity',$activity)->limit(10)->get();
        foreach ($banner2 as $key => $val) {
           $banner2[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        $banner3  = BannerModel::where(['active'=>1,'banner_type'=>4])->whereIn('type',$type)->whereIn('activity',$activity)->limit(10)->get();
        foreach ($banner3 as $key => $val) {
           $banner3[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        $banner4  = BannerModel::where(['active'=>1,'banner_type'=>5])->whereIn('type',$type)->whereIn('activity',$activity)->limit(10)->get();
        foreach ($banner4 as $key => $val) {
           $banner4[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        $banner5  = BannerModel::where(['active'=>1,'banner_type'=>6])->whereIn('type',$type)->whereIn('activity',$activity)->limit(10)->get();
        foreach ($banner5 as $key => $val) {
           $banner5[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }
            
            $o_data['banner'] = convert_all_elements_to_string($banner->toArray());
            $o_data['popular_categories'] = convert_all_elements_to_string($subcategorieslist);
            $o_data['offer_banners'] = convert_all_elements_to_string($banner1->toArray());
            $o_data['most_booked_list'] = convert_all_elements_to_string($services_booked);
            $o_data['single_banner'] = convert_all_elements_to_string($banner2->toArray());
            $o_data['new_service_list'] = convert_all_elements_to_string($services);
            $o_data['service_categories1'] = convert_all_elements_to_string($all_service_cat1);
            $o_data['service_categories2'] = convert_all_elements_to_string($all_service_cat2);
            $o_data['middle_banner1'] = convert_all_elements_to_string($banner3->toArray());
            $o_data['service_categories3'] = convert_all_elements_to_string($all_service_cat3);
            $o_data['service_categories4'] = convert_all_elements_to_string($all_service_cat4);
            $o_data['middle_banner2'] = convert_all_elements_to_string($banner4->toArray());
            $o_data['service_categories5'] = convert_all_elements_to_string($all_service_cat5);
            $o_data['service_categories6'] = convert_all_elements_to_string($all_service_cat6);
            $o_data['middle_banner3'] = convert_all_elements_to_string($banner5->toArray());
            
            
            
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function offer_list(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $o_data['offer_banners'] = (object)[];
        $errors = [];
        $validator = Validator::make($request->all(), [
           
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
        $service_categories = ServiceCategories::where(['active'=>1,'deleted'=>0])->get();

        $page = $request->page ?? 0;
        if (isset($page))
            $page = ($page==0) ? 0 : $page-1;

        $limit = $request->limit ?? 1000000;
        $offset = $page * $limit;

        $all_service_cat = [];
        foreach ($service_categories as $key_main => $value) {

            $services = Service::select('service.*','service_category_selected.*')->where(['active'=>1,'deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')->limit(10)
        ->where('service_category_selected.category_id',$value->id);
        if(!empty($request->search))
        {
        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        $services = $services->distinct('service_category_selected.service_id')
        ->get();

        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            $services[$key]->hourly_rate = $hourly_rate;
            $service_categories[$key_main]['service_list'] = $services;
        }
        
        if(!empty($services) && count($services) > 0)
        {
            $all_service_cat = $service_categories;
        }
        $all_service_cat1 = [];
        $all_service_cat2 = [];
        $all_service_cat3 = [];
        $all_service_cat4 = [];
        $all_service_cat5 = [];
        $all_service_cat6 = [];
        $all_service_cat7 = [];
        foreach ($all_service_cat as $key => $value) {
            if($key <= 0)
            {
                $all_service_cat1[] =  $value; 
            }
            if($key > 0 && $key <= 1)
            {
                $all_service_cat2[] =  $value; 
            }
            if($key > 1 && $key <= 2)
            {
                $all_service_cat3[] =  $value; 
            }
            if($key > 2 && $key <= 3)
            {
                $all_service_cat4[] =  $value; 
            }
            if($key > 3 && $key <= 4)
            {
                $all_service_cat5[] =  $value; 
            }
            if($key > 4 && $key <= 5)
            {
                $all_service_cat6[] =  $value; 
            }
           

        }

          
        }


        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0])->first();
        
        
        if(!empty($categories))
        {
          
            $services = Service::select('service.*','service_category_selected.*')->where(['active'=>1,'deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')->limit(10);
        //->join('service_price','service_price.service_id','=','service.id')
        //->where('service_category_selected.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->distinct('service_category_selected.service_id')
        ->get();
        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            $services[$key]->hourly_rate = $hourly_rate;
        }

        $subcategories = ServiceCategories::where(['active'=>1,'deleted'=>0])->limit(10)->get();
        $subcategorieslist = [];
        $key = 0;
       
        $type = [3,4,1,2];
        $activity = [0,6,7];
       

        $banner1  = BannerModel::where(['active'=>1,'banner_type'=>2])->whereIn('type',$type)->skip($offset)
        ->take($limit)->get();
        foreach ($banner1 as $key => $val) {
           $banner1[$key]->banner_image = url(config('global.upload_path').config('global.banner_image_upload_dir').$val->banner_image); 
        }

        
            
        
            $o_data['offer_banners'] = convert_all_elements_to_string($banner1->toArray());
           
            
            
            
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function new_service_list(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
           
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $page = $request->page ?? 0;
        if (isset($page))
            $page = ($page==0) ? 0 : $page-1;

        $limit = $request->limit ?? 10;
        $offset = $page * $limit;
        
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
        $service_categories = ServiceCategories::where(['active'=>1,'deleted'=>0])->get();

        
        $all_service_cat = [];
        $services = [];
       


        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0])->first();
        
        
        if(!empty($categories))
        {
           
            $services = Service::select('service.*','service_category_selected.*')->where(['service.active'=>1,'service.deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')
        ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
        ->orderBy('service.id','desc');
        
        
        //->join('service_price','service_price.service_id','=','service.id')
        //->where('service_category_selected.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->skip($offset)
        ->take($limit)->distinct('service.id')
        ->get();
        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            $services[$key]->hourly_rate = $hourly_rate;
        }

      
            
            
            $o_data['new_service_list'] = convert_all_elements_to_string($services);
          
            
        }
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function most_booked_service(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
           
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        
      
       //most booked
       $services_booked = Service::select('service.*','service.id as service_id','service.id as category_id')
       ->join('service_category_selected','service_category_selected.service_id','=','service.id')
       ->leftjoin('service_category','service_category.id','=','service_category_selected.category_id')
       ->where(['service.active'=>1,'service.deleted'=>0,'service_category.activity_id'=>6])
       ->where('order_count','>',0)->limit(100);
       //->join('service_price','service_price.service_id','=','service.id')
       //->where('service_category_selected.category_id',$request->category_id);
       if(!empty($request->search))
       {

       $services_booked =$services_booked->where('service.name', 'ilike', '%'.$request->search.'%');
       }
       //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
       $services_booked = $services_booked->orderBy('order_count','desc')
       ->get();
       foreach ($services_booked as $key => $value) {
           $services_booked[$key]->regular_price = (string) 0;
           $services_booked[$key]->description = (string) $value->description;
           $where['service_id']   = $value->id;
           $services_booked[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
           $services_booked[$key]->rating_count = Rating::where($where)->get()->count();
           $ratingdata = Rating::rating_list($where);
          
           $services_booked[$key]->rating_details = $ratingdata;
           // if(!empty($request->city_id))
           // {
           // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
           // }
           // if(!empty($pricecity))
           // {
           //  $services[$key]->service_price  = $pricecity->service_price;
           //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
           // }
           $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->id])->get();
           $services_booked[$key]->hourly_rate = $hourly_rate;
       }
       //most booked
       

       
       
            
            
            $o_data['most_booked_list'] = convert_all_elements_to_string($services_booked);
           
            
            
            
        
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function service_sub_categories(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'parent_id'=>$request->category_id])->get();
        
        $categorieslist = [];
        $key = 0;
        foreach ($categories as $value) {
            $count = ServiceCategories::join('service_category_selected','service_category_selected.category_id','=','service_category.id')
            ->join('service','service.id','=','service_category_selected.service_id')
            //->join('service_price','service_price.service_id','=','service.id')
            ->where('service.active',1)
            ->whereRaw("service.id in (select service_id from service_category_selected where category_id = '".$value->id."' or category_id in (select id from service_category where parent_id='".$value->id."')) ")
            //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
            ->get()->count();
            
            if($count > 0)
            {
              $categorieslist[$key] = new \stdClass;
              $categorieslist[$key]->id = $value->id;
              $categorieslist[$key]->name = $value->name;
              $categorieslist[$key]->image = $value->image;
              $categorieslist[$key]->description = (string) $value->description;  
              $key++;
            }
            
        }

        $o_data['list'] = convert_all_elements_to_string($categorieslist);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function servicelist(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $services_id = [];
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $services = Service::select('service.*','service_category_selected.*')->where(['active'=>1,'deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')->orderBy('sort_order','asc')
        //->join('service_price','service_price.service_id','=','service.id')
        ->where('service_category_selected.category_id',$request->category_id);
        if(!empty($request->search))
        {

        $services =$services->where('service.name', 'ilike', '%'.$request->search.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->distinct('service_category_selected.service_id','sort_order')
        ->get();
        foreach ($services as $key => $value) {
            $services[$key]->regular_price = (string) 0;
            $services[$key]->description = (string) $value->description;
            $where['service_id']   = $value->service_id;
            $services[$key]->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services[$key]->rating_count = Rating::where($where)->get()->count();
            $ratingdata = Rating::rating_list($where);
           
            $services[$key]->rating_details = $ratingdata;
            // if(!empty($request->city_id))
            // {
            // $pricecity = ServicePrice::where(['service_id'=>$value->service_id,'city'=>$request->city_id])->get()->first();   
            // }
            // if(!empty($pricecity))
            // {
            //  $services[$key]->service_price  = $pricecity->service_price;
            //  $services[$key]->regular_price  = (string) $pricecity->regular_price??0;
            // }
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$value->service_id])->get();
            if(!empty($hourly_rate) && count($hourly_rate) > 0)
            {
                // Create the new data array
                   $dataArray = [
                  'text' => 'basic price',
                  'hourly_rate' => $value->service_price
                 ];


                  $hourly_rate->prepend($dataArray);
            }
            $services[$key]->hourly_rate = $hourly_rate;
            $services_id[] = $value->service_id;
        }
        $categories = ServiceCategories::where(['active'=>1,'deleted'=>0,'id'=>$request->category_id])->first();
        $o_data  = convert_all_elements_to_string($categories->toarray());
        $o_data['list'] = convert_all_elements_to_string($services);
        $where = $services_id;
            $ratingdata = Rating::rating_list_by_services($services_id);
            $ratingavg = Rating::avg_rating_wherein($services_id);
            $o_data['avg_rating'] = (string) $ratingavg;
            $o_data['rating'] = convert_all_elements_to_string($ratingdata);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }

    public function servicesearch(Request $request)
    {
        
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $services_id = [];
        $validator = Validator::make($request->all(), [
            'activity_type_id' => 'required',
            'search_key'=>'required'
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $activity_type_id = $request->activity_type_id;
        $search_key = $request->search_key;
        $page =$request->page??1;
        $limit=$request->limit??10;
        $offset = ($page - 1) * $limit;

        $services = Service::select('service.*')->where(['service.active'=>1,'service.deleted'=>0])
        ->join('service_category_selected','service_category_selected.service_id','=','service.id')
        ->join('service_category','service_category.id','=','service_category_selected.category_id')
        ->join('vendor_services','vendor_services.service_id','=','service.id')
        ->where('service_category.activity_id',$request->activity_type_id);
        if(!empty($request->search_key))
        {

            $services =$services->where('service.name', 'ilike', '%'.$request->search_key.'%');
        }
        //->where(['service_price.state'=>$request->emirate_id,'service_price.city'=>$request->city_id])
        $services = $services->distinct('service_category_selected.service_id')->take($limit)->skip($offset)
        ->get();
        if($services->count() > 0){
            $o_data['list'] = convert_all_elements_to_string($services->toArray());
            $status = "1";
            $message = "data listed";
        }else{
            $status = "0";
            $message = "no data to list";
        }
        
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object)$o_data], 200);
    }

    public function serviceDetails(Request $request)
    {
        $status = "1";
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }
        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();

        $services = Service::select('service.*')->where(['active'=>1,'deleted'=>0,'service.id'=>$request->service_id])->first();
        
        if($services)
        {
            $services->description = (string) $services->description;
            $where['service_id']   = $services->id;
            $services->rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $services->rating_count = Rating::where($where)->get()->count();

            $parent_id = ServiceCategorySelected::where('service_id',$services->id)
            ->leftjoin('service_category','service_category.id','service_category_selected.category_id')->first()->parent_id??0;
            $services->subcategory_id = $parent_id == 0 ? 0 : 1; ;
            $services  = convert_all_elements_to_string($services->toArray());
            $ratingdata = Rating::rating_list($where);
          
            $services['rating_details'] = convert_all_elements_to_string($ratingdata);
           
            $hourly_rate = HourlyRate::select('text','hourly_rate')->where(['service_id'=>$services['id']])->get();
           
            if(!empty($hourly_rate) && count($hourly_rate) > 0)
            {
                // Create the new data array 
                   $dataArray = [
                  'text' => $services['price_label']??'basic price',
                  'hourly_rate' => $services['service_price']
                 ];

                    //removed on 03-05-2024 hamid said
                  //$hourly_rate->prepend($dataArray);
            }
            $services['hourly_rate'] = convert_all_elements_to_string($hourly_rate);
            $o_data = $services;
        }
         
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
    public function featured_service_details(Request $request)
    {

        $status = "1";
        $message = "";
        $errors = [];
        $product = [];
        $o_data = [];
        $stores = [];
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|numeric|min:0|not_in:0',
        ]);

        if ($validator->fails()) {
            $message = "Validation error occured";
            $errors = $validator->messages();
            return response()->json([
                'status' => "0",
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        $access_token = $request->access_token;
        $user = User::where('user_access_token', $access_token)->first();
        $service_id = $request->service_id;

        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;



        $featuredservice = Service::where(['active' => 1, 'deleted' => 0, 'service.id' => $service_id])
            ->join('service_category_selected', 'service_category_selected.service_id', '=', 'service.id')
            ->first();


        if ($featuredservice) {
            if (!empty($request->city_id)) {
                $pricecity = ServicePrice::where(['service_id' => $featuredservice->service_id, 'city' => $request->city_id])->get()->first();
            }
            if (!empty($pricecity)) {
                $featuredservice->service_price  = $pricecity->service_price;
            }

            $featured_services  = Service::where(['active' => 1, 'deleted' => 0])
                ->join('service_price', 'service_price.service_id', '=', 'service.id')
                ->where(['service_price.state' => $request->emirate_id, 'service_price.city' => $request->city_id])
                ->limit($limit)->skip($offset)->get();
            foreach ($featured_services as $key => $value) {
                if (!empty($request->city_id)) {
                    $pricecity = ServicePrice::where(['service_id' => $value->id, 'city' => $request->city_id])->get()->first();
                }
                if (!empty($pricecity)) {
                    $featured_services[$key]->service_price  = $pricecity->service_price;
                }
            }




            $status = "1";
            $o_data['service_details']        = $featuredservice;
            $o_data['featured_services']      = $featured_services;
        } else {
            $status = "0";
            $product = [];
            $message = "No Service found.";
        }


        $o_data = convert_all_elements_to_string($o_data);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
}