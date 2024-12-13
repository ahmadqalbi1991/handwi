<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\ModaSubCategories;
use App\Models\Likes;
use App\Models\ProductModel;
use App\Models\FeaturedProducts;
use App\Models\FeaturedProductsImg;
use App\Models\Stores;
use App\Models\VendorDetailsModel;
use App\Models\User;
use App\Models\Rating;
use App\Models\Categories;
use App\Models\Cuisine;
use DB;
use Illuminate\Http\Request;
use Validator;

class ProductController extends Controller
{
    private function validateAccesToken($access_token)
    {

        $user = User::where(['user_access_token' => $access_token])->get();

        if ($user->count() == 0) {
            http_response_code(401);
            echo json_encode([
                'status' => (string) 0,
                'message' => login_message(),
                'oData' => [],
                'errors' => (object) [],
            ]);
            exit;
        } else {
            $user = $user->first();
            if ($user->active == 1) {
                return $user->id;
            } else {
                http_response_code(401);
                echo json_encode([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ]);
                exit;
                return response()->json([
                    'status' => (string) 0,
                    'message' => login_message(),
                    'oData' => [],
                    'errors' => (object) [],
                ], 401);
                exit;
            }
        }
    }
    function product_filters(Request $request)
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
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        $where['deleted'] = 0;
        $where['product_status'] = 1;

        if($request->vendor_id){
            $where['product_vender_id']   = $request->vendor_id;
        }
        if($request->category_id){
            // $where['category_id'] = $request->category_id;
        }

        $categories = Categories::whereHas('product_categories.product',function($q) use($request){
            $q->when($request->vendor_id,function($qq) use($request){
                $qq->where('product_vender_id',$request->vendor_id);
            });
        })
        ->where(['deleted' => '0','active' => '1'])->orderBy('sort_order', 'asc')->select('id','name','image','banner_image','activity_id');
        if($request->activity_id){
            $categories->where('activity_id',$request->activity_id);
        }
        $categories = $categories->get();


        $Cuisine = Cuisine::whereHas('product_cuisine.product',function($q) use($request){
            $q->when($request->vendor_id,function($qq) use($request){
                $qq->where('product_vender_id',$request->vendor_id);
            });
        })->where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->select('id','name');
        $Cuisine = $Cuisine->get();


        $min_products = ProductModel::where($where)->select('product_category.*','product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended','product_selected_attribute_list.*')
        ->join("product_selected_attribute_list","product_selected_attribute_list.product_id","=","product.id")
        ->join("product_category","product_category.product_id","=","product.id");
        if($request->category_id){
            $cat_id = explode(',', $request->category_id);
            $min_products->whereIn("product_category.category_id",$cat_id);
            // ->whereIn('product.id',function($query) use ($cat_id){
            //     $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
            // });
        }
        if($request->cuisines_ids){
            $cuisine_id = explode(',', $request->cuisines_ids);
            $min_products->whereHas('product_cuisine',function($c_q) use($cuisine_id){
                return $c_q->whereIn("cuisine_id",$cuisine_id);
            });
        }
        $min_price = $min_products->orderBy('sale_price','asc')->first()->sale_price ?? 0;

        $max_products = ProductModel::where($where)->select('product_category.*','product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended','product_selected_attribute_list.*')
        ->join("product_selected_attribute_list","product_selected_attribute_list.product_id","=","product.id")
        ->join("product_category","product_category.product_id","=","product.id");

        if($request->category_id){
            $cat_id = explode(',', $request->category_id);
            $max_products->whereIn("product_category.category_id",$cat_id);
            // ->whereIn('product.id',function($query) use ($cat_id){
            //     $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
            // });
        }
        if($request->cuisines_ids){
            $cuisine_id = explode(',', $request->cuisines_ids);
            $max_products->whereHas('product_cuisine',function($c_q) use($cuisine_id){
                return $c_q->whereIn("cuisine_id",$cuisine_id);
            });
        }
        $max_price = $max_products->orderBy('sale_price','desc')->first()->sale_price ?? 0;

        $o_data['min_price']    = $min_price;
        $o_data['max_price']    = $max_price;
        $o_data['categories']   = $categories;
        $o_data['Cuisine']      = $Cuisine;


        if(request()->test){
            $ps  = ProductModel::get();
            foreach ($ps as $key => $p) {
                if(Rating::where(['product_id'=>$p->id])->get()->count()){
                    $p_avg = number_format(Rating::avg_rating(['product_id'=>$p->id]), 1, '.', '');
                    if($p){
                        $p->rating_avg = $p_avg;
                        $p->save();
                    }
                }
            }

        }
        
        $o_data = convert_all_elements_to_string($o_data);
        $o_data['categories']   = $categories->count() ? convert_all_elements_to_string($categories) : [];
        $o_data['Cuisine']      = $Cuisine->count() ? convert_all_elements_to_string($Cuisine) : [];
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => ($o_data)], 200);
    }
    function list(Request $request)
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
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        $where['deleted'] = 0;
        $where['product_status'] = 1;

        $filter['search_text'] = $request->search_text;
        $filter['vendor_id']   = $request->vendor_id;
        $filter['activity_id'] = $request->activity_id;
        $filter['category_id'] = $request->category_id;
        $filter['category_ids'] = $request->category_ids;
        $filter['start_price'] = $request->start_price;
        $filter['end_price'] = $request->end_price;
        $filter['ratting'] = $request->ratting;
        $filter['cuisines_ids'] = $request->cuisines_ids;

        $list = ProductModel::products_list($where, $filter, $limit, $offset)->get();
        $user = User::where('user_access_token', $access_token)->first();
        $products = $this->product_inv($list, $user);
        $o_data['list'] = $products->count() ? convert_all_elements_to_string($products) : [];
        // $o_data = ($o_data);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }

    public function product_inv($products, $user)
    {
        foreach ($products as $key => $val) {
            $products[$key]->is_liked = 0;
            $where['product_id'] = $val->id;
            $products[$key]->avg_rating = number_format(Rating::avg_rating($where), 1, '.', '');
            $products[$key]->rating_count = Rating::where($where)->get()->count();
            if(request()->test){
                $products[$key]->ratings_list = Rating::where(['product_id'=>$val->id])->get();
            }
            if ($user) {
                $is_liked = Likes::where(['product_id' => $val->id, 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $products[$key]->is_liked = 1;
                }
            }
            $det = [];
            if ($val->default_attribute_id) {
                $det = DB::table('product_selected_attribute_list')->select('product_attribute_id', 'stock_quantity', 'sale_price', 'regular_price', 'image', 'product_full_descr')->where('product_id', $val->id)->where('product_attribute_id', $val->default_attribute_id)->first();
                if ($det) {
                    $images = $det->image;
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];
                        foreach ($images as $img) {
                            if ($img) {
                                $prd_img[$i] = get_uploaded_image_url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') .$img);//url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);
                                // dd($prd_img[$i],config('global.upload_path') .  config('global.product_image_upload_dir') . $img);
                                $i++;
                            }
                        }
                        $det->sale_price = number_format($det->sale_price, 2, '.', '');
                        $det->regular_price = number_format($det->regular_price, 2, '.', '');
                        $det->image = $prd_img;
                    } else {
                        $det->image = [];
                    }
                }else{
                    $det = DB::table('product_selected_attribute_list')->select('product_attribute_id', 'stock_quantity', 'sale_price', 'regular_price', 'image', 'product_full_descr')->where('product_id', $val->id)->orderBy('product_attribute_id', 'DESC')->limit(1)->first();

                    if ($det) {
                        $images = $det->image;
                        if ($images) {
                            $images = explode(',', $images);
                            $i = 0;
                            $prd_img = [];
    
                            foreach ($images as $img) {
                                if ($img) {
                                    $prd_img[$i] = get_uploaded_image_url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);//url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);
                                    $i++;
                                }
                            }
                            $det->sale_price = number_format($det->sale_price, 2, '.', '');
                            $det->regular_price = number_format($det->regular_price, 2, '.', '');
                            $det->image = $prd_img;
                        } else {
                            $det->image = [];
                        }
                    }
                }
            } else {
                $det = DB::table('product_selected_attribute_list')->select('product_attribute_id', 'stock_quantity', 'sale_price', 'regular_price', 'image', 'product_full_descr')->where('product_id', $val->id)->orderBy('product_attribute_id', 'DESC')->limit(1)->first();

                if ($det) {
                    $images = $det->image;
                    if ($images) {
                        $images = explode(',', $images);
                        $i = 0;
                        $prd_img = [];

                        foreach ($images as $img) {
                            if ($img) {
                                $prd_img[$i] = get_uploaded_image_url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);//url(config('global.upload_path') . '/' . config('global.product_image_upload_dir') . $img);
                                $i++;
                            }
                        }
                        $det->sale_price = number_format($det->sale_price, 2, '.', '');
                        $det->regular_price = number_format($det->regular_price, 2, '.', '');
                        $det->image = $prd_img;
                    } else {
                        $det->image = [];
                    }
                }
            }
            $products[$key]->inventory = $det;
        }
        return $products;
    }
    public function product_like_dislike(REQUEST $request)
    {
        $status = (string) 0;
        $message = "";
        $o_data = [];
        $errors = [];

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'product_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $status = (string) 0;
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $user_id = $this->validateAccesToken($request->access_token);
            $product_id = $request->product_id;
            $check_exist = ProductLikes::where(['product_id' => $product_id, 'user_id' => $user_id])->get();
            if ($check_exist->count() > 0) {
                ProductLikes::where(['product_id' => $product_id, 'user_id' => $user_id])->delete();
                $status = (string) 1;
                $message = "disliked";
            } else {
                $like = new ProductLikes();
                $like->product_id = $product_id;
                $like->user_id = $user_id;
                $like->created_at = gmdate('Y-m-d H:i:s');
                $like->save();
                if ($like->id > 0) {
                    $status = (string) 1;
                    $message = "liked";
                } else {
                    $message = "faild to like";
                }
            }
        }
        return response()->json(['status' => $status, 'error' => $errors, 'message' => $message, 'oData' => $o_data], 200);
    }

    public function details(Request $request)
    {

        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $product = [];
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|min:0|not_in:0',
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
        $product_id = $request->product_id;
        $product_variant_id = $request->product_variant_id;

        $sattr = $request->sattr;
        $sattr = json_decode($request->sattr, true);
        $return_status = true;

        if (!$product_variant_id) {
            list($return_status, $product_attribute_id, $message) = ProductModel::get_product_attribute_id_from_attributes($sattr, $product_id);
            $product_variant_id = $product_attribute_id;
        }

        if (!$return_status) {
            $status = (string) 0;
            $message = "Invalid data passed";
            return response()->json([
                'status' => 0,
                'message' => $message,
                'errors' => (object) $errors,
            ], 200);
        }

        list($status, $product, $message) = ProductModel::getProductVariant($product_id, $product_variant_id);


        if ($status && !empty($product)) {
            $product = process_product_data_api($product);
            $product['is_liked'] = 0;
            $where['product_id']   = $product['product_id'];
            $product['avg_rating'] = number_format(Rating::avg_rating($where), 1, '.', '');
            $product['rating_count'] = Rating::where($where)->get()->count();
            if ($user) {
                $is_liked = Likes::where(['product_id' => $product['product_id'], 'user_id' => $user->id])->count();
                if ($is_liked) {
                    $product['is_liked'] = 1;
                }
            }


            $product['share_link'] = url("share/product/" . $product_id . "/" . $product['product_variant_id']);

            $product['specifications'] = convert_all_elements_to_string(ProductModel::get_product_specs($product_id));

            $product_selected_attributes = ProductModel::getProductVariantAttributes($product['product_variant_id']);
            //printr($product_selected_attributes);
            $product_variations = [];
            $product_attributes = ProductModel::getProductAttributeVals([$product['product_id']]);
            foreach ($product_attributes as $attr_row) {
                if (array_key_exists($attr_row->attribute_id, $product_variations) === false) {
                    $product_variations[$attr_row->attribute_id] = [
                        'product_attribute_id' => $attr_row->product_attribute_id,
                        'attribute_id' => $attr_row->attribute_id,
                        'attribute_values_id' => $attr_row->attribute_values_id,
                        'attribute_type' => $attr_row->attribute_type,
                        'attribute_name' => $attr_row->attribute_name,
                        'attribute_values' => [],
                    ];
                    if ($attr_row->attribute_type === 'radio_button_group') {
                        $product_variations[$attr_row->attribute_id]['help_text_start'] = $attr_row->attribute_value_label;
                    }
                }
                if ($attr_row->attribute_type === 'radio_button_group') {
                    $product_variations[$attr_row->attribute_id]['help_text_end'] = $attr_row->attribute_value_label;
                }
                
                if (array_key_exists($attr_row->attribute_values_id, $product_variations[$attr_row->attribute_id]['attribute_values']) === false) {
                    $is_selected = 0;
                    if (array_key_exists($attr_row->attribute_id, $product_selected_attributes) && ($product_selected_attributes[$attr_row->attribute_id] == $attr_row->attribute_values_id)) {
                        $is_selected = 1;
                    }
                    $product_variations[$attr_row->attribute_id]['attribute_values'][$attr_row->attribute_values_id] = [
                        'attribute_value_id' => $attr_row->attribute_values_id,
                        'attribute_value_name' => $attr_row->attribute_values,
                        'product_attribute_id' => $attr_row->product_attribute_id,
                        'is_selected' => $is_selected,
                        't'=>$product_selected_attributes[$attr_row->attribute_id]
                    ];
                    if ($attr_row->attribute_value_in == 2) {
                        $product_variations[$attr_row->attribute_id]['attribute_values'][$attr_row->attribute_values_id]['attribute_value_color'] = $attr_row->attribute_color;
                    }
                    if ($attr_row->attribute_type === 'radio_image') {
                        $t_image = $attr_row->attribute_value_image;

                        $product_variations[$attr_row->attribute_id]['attribute_values'][$attr_row->attribute_values_id]['attribute_value_image'] = $t_image;
                    }
                }
            }

            $product['product_variations'] = [];
            if (!empty($product_variations)) {
                $t_variations = array_values($product_variations);
                foreach ($t_variations as $k => $v) {
                    $t_variations[$k]['attribute_values'] = array_values($t_variations[$k]['attribute_values']);
                }
                $product["product_variations"] = convert_all_elements_to_string($t_variations);
            }

            $variable_products = ProductModel::select('product.id', 'product.product_name', 'product.product_type', 'product_selected_attribute_list.product_attribute_id as default_attribute_id', 'product.boxcount')
                ->join('product_selected_attribute_list', 'product_selected_attribute_list.product_id', '=', 'product.id')
                ->where('product.product_status', 1)->where('product.deleted', 0)->where('product_vender_id', $product['product_vendor_id'])->orderBy('created_at', 'desc')
                ->where('product.id', '=', $product_id)
                ->where('product_selected_attribute_list.product_attribute_id', '!=', $product_variant_id)->limit(4)->get();
                
            $pros = $this->product_inv($variable_products, $user);
            $product['variants_list'] = count($pros) ? convert_all_elements_to_string($pros) : [];
            

            $shop_products = ProductModel::select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id', 'product.boxcount')->where('product.product_status', 1)->where('product.deleted', 0)->where('product_vender_id', $product['product_vendor_id'])->orderBy('created_at', 'desc')->where('product.id', '!=', $product_id)->limit(4)->get();

            $product['shop_products'] = $shop_products->count() ? convert_all_elements_to_string($this->product_inv($shop_products, $user)) : [];
        } else {
            $status = (string) 0;
            $product = [];
            $message = "No details found.";
        }

        $o_data['product_details'] = convert_all_elements_to_string($product);
        $product  = convert_all_elements_to_string($product);
        if(count( (array)$product['rating_details'])  == 0)
        {
            $product['rating_details'] = [];  
        }
        return response()->json(['status' => (int)$status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $product], 200);
    }
    public function featured_product_details(Request $request)
    {

        $status = (string) 1;
        $message = "";
        $errors = [];
        $product = [];
        $o_data = [];
        $stores = [];
        $validator = Validator::make($request->all(), [
            'master_product_id' => 'required|numeric|min:0|not_in:0',
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
        $master_product_id = $request->master_product_id;


        // $featuredproduct = FeaturedProducts::select('featured_products.*','product_master.name','featured_products.id as id')
        // ->join('product_master','product_master.id','=','featured_products.master_product')
        // ->join('product','product.master_product','=','featured_products.master_product')
        // ->where(['product.deleted'=>0,'product.product_status'=>1])
        // ->distinct('product_master.id')->first();
        $featuredproduct = FeaturedProducts::select('featured_products.*', 'product_master.name')->where('featured_products.master_product', $master_product_id)
            ->join('product_master', 'product_master.id', '=', 'featured_products.master_product')->first();

        if ($featuredproduct) {

            $img = [];
            $featuredproductimage   = FeaturedProductsImg::select('image')->where('featured_product_id', $featuredproduct->id)->get();
            foreach ($featuredproductimage as $key => $value) {
                $img[] = asset($value->image);
            }

            $product = $featuredproduct;
            $product->image = $img;

            $where = [];
            $limit = isset($request->limit) ? $request->limit : 10;
            $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;
            $filter['master_product_id'] = $request->master_product_id;
            $stores = VendorDetailsModel::get_stores_for_featured($where, $filter, $limit, $offset)->get();

            foreach ($stores as $key => $val) {
                $stores[$key]->logo = asset($val->logo);
                $stores[$key]->cover_image = asset($val->cover_image);
                $stores[$key]->is_liked = 0;
                $stores[$key]->rating = 0;
                if ($user) {
                    $is_liked = Likes::where(['vendor_id' => $val->id, 'user_id' => $user->id])->count();
                    if ($is_liked) {
                        $stores[$key]->is_liked = 1;
                    }
                }
            }

            $status = (string) 1;
            $o_data['product_details'] = $product;
            $o_data['pharmacies']      = $stores;
            if (empty($stores)) {
                $o_data['pharmacies']      = [];
            }
        } else {
            $status = (string) 0;
            $product = [];
            $message = "No product found.";
        }


        $o_data = convert_all_elements_to_string($o_data);
        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => (object) $o_data], 200);
    }

    public function list_moda_products(Request $request)
    {
        $status = (string) 1;
        $message = "";
        $o_data = [];
        $errors = [];
        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'moda_sub_category' => 'required|numeric',
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

        $user_id = $this->validateAccesToken($request->access_token);
        $user = User::where('id', $user_id)->first();
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->page) ? ($request->page - 1) * $request->limit : 0;

        $where['product.deleted'] = 0;
        $where['product.product_status'] = 1;
        $where['product.moda_sub_category'] = $request->moda_sub_category;
        $where['moda_sub_categories.gender'] = $user->gender;

        $filter['search_text'] = $request->search_text;
        $filter['store_id'] = $request->store_id;
        $filter['sort_by_price'] = $request->sort_by_price;
        $filter['sort_by_newest'] = $request->sort_by_newest;

        $list = ProductModel::moda_products_list($where, $filter, $limit, $offset)->get();

        $products = $this->product_inv($list, $user);
        $moda_sub_category = ModaSubCategories::select('id', 'name')->where('id', $request->moda_sub_category)->first();
        $o_data['moda_sub_category'] = $moda_sub_category;
        $o_data['list'] = $products;

        return response()->json(['status' => $status, 'message' => $message, 'errors' => (object) $errors, 'oData' => $o_data], 200);
    }
}