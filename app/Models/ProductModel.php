<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Attribute;
use App\Models\ProductAttribute;
class ProductModel extends Model
{
    //
    protected $table = "product";
    protected $primaryKey = "id";
    public $path = "https://d3yyal9qow7g9.cloudfront.net/products/";
    public $timestamps = false;


    public $fillable = [
        'name',
        'description',
        'active',
        'deleted',
        'sort_order',
        'created_by',
        'created_on',
        'updated_by',
        'updated_on',
        'image_path',
        'product_vender_id'
    ];

    public function vendor()
    {
        return $this->hasOne(User::class,'id','product_vender_id');
    }
     public function product_cuisine() {
       return $this->hasMany(ProductCuisines::class,'product_id');
    }
    public function product_category()
    {
        return $this->hasMany(ProductCategory::class,'product_id','id');
    }
    public function variants()
    {
        return $this->hasMany(ProductSelectedAttributeList::class,'product_id','id');
    }
    public function product_attribute()
    {
        return $this->hasMany(ProductAttribute::class,'product_id','id');
    }
    public static function save_product($ins=[],$category_ids=[],$specs=[],$spec_doc_ins=[]){
        DB::beginTransaction();

        try {
            $product_id = DB::table('product')-> insertGetId($ins);

            if(!empty($category_ids)){
                foreach($category_ids as $cat){
                    DB::table('product_category')-> insertGetId(['category_id'=>$cat,'product_id'=>$product_id]);
                }
            }
            if(!empty($specs)){
                foreach($specs as  $d => $v){ 
                    DB::table('product_specs')-> insertGetId(['title'=>$v['title'],'description'=>$v['description'],'product_id'=>$product_id]);
                }
            }
            if(!empty($spec_doc_ins)){
                foreach($spec_doc_ins as  $d){ 
                    DB::table('product_docs')-> insertGetId(['title'=>$d['title'],'doc_path'=>$d['doc_path'],'product_id'=>$product_id]);
                }
            }

            DB::commit();
            return $product_id;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            DB::rollback();
            return 0;
        }
    }

    public static function update_product($product_id,$ins=[],$category_ids=[],$specs=[],$spec_doc_ins=[],$data = []){
        DB::beginTransaction();

        try {  
            DB::table('product')->where('id',$product_id)->update($ins);

            if(!empty($category_ids)){
                DB::table('product_category')->where('product_id', '=', $product_id)->delete();
                foreach($category_ids as $cat){
                    DB::table('product_category')->insert(['category_id'=>$cat,'product_id'=>$product_id]);
                }
            }
            ProductCuisines::where('product_id',$product_id)->delete();
            if ( isset($data['cuisines']) && !empty($data['cuisines']) ) {
                foreach ($data['cuisines'] as $key => $row) {
                    $Cuisine = new ProductCuisines();
                    $Cuisine->product_id = $product_id;
                    $Cuisine->cuisine_id = $row;
                    $Cuisine->save();
                }
            }
            DB::table('product_specifications')->where('product_id',$product_id)->delete();
            if(!empty($specs)){
                foreach($specs as  $d => $v){ 
                    DB::table('product_specifications')-> insert(['spec_title'=>$v['title'],'spec_descp'=>$v['description'],'product_id'=>$product_id]);
                }
            }
            if(!empty($spec_doc_ins)){
                foreach($spec_doc_ins as  $d){ 
                    DB::table('product_docs')-> insert(['title'=>$d['title'],'doc_path'=>$d['doc_path'],'product_id'=>$product_id]);
                }
            }
            if ( isset($data['product_simple_variant']) && !empty($data['product_simple_variant']) ) {  
                $checkExist = DB::table('product_selected_attribute_list')->where('product_id',$product_id)->get();
                if(!empty(count($checkExist))) {
                    DB::table('product_selected_attribute_list')->where('product_id',$product_id)->update($data['product_simple_variant']);
                } else {
                    $data['product_simple_variant']['product_id'] = $product_id;
                    DB::table('product_selected_attribute_list')->insert($data['product_simple_variant']);
                }

            }
            if ( isset($data['product_multi_variant']) && !empty($data['product_multi_variant']) ) {
                foreach ($data['product_multi_variant'] as $t_attribute) {
                    $defaultAttribute = $t_attribute['default_attribute_id'] ?? '';
                    unset($t_attribute['default_attribute_id']);
                    $checkExist = DB::table('product_selected_attribute_list')->where('product_attribute_id',$t_attribute['product_attribute_id'])->get()->first(); 
                       if ( isset($t_attribute['product_attribute_id']) && ($t_attribute['product_attribute_id'] > 0) ) {
                            $product_attribute_id = $t_attribute['product_attribute_id'];
                             $attValues  = $t_attribute['attribute_values'];
                             unset($t_attribute['attribute_values']);
                             if(!empty($checkExist->image))
                             {
                             $t_attribute['image'] = $checkExist->image.",".$t_attribute['image'];   
                             }
                             else
                             {
                              $t_attribute['image'] = $t_attribute['image'];     
                             }
                             
                             DB::table('product_selected_attribute_list')->where('product_attribute_id',$product_attribute_id)->update($t_attribute);
                            if($defaultAttribute >= 1) {
                                self::updateDefaultAttribute($product_id,$product_attribute_id);
                            }
                            
                        } else { 
                            $t_attribute_val_records = $t_attribute['attribute_values'];
                            $t_attribute_val_records = self::getAttributeValuesByIds($t_attribute['attribute_values']); 
                           
                            unset($t_attribute['attribute_values']);
                            $t_attribute['product_id'] = $product_id; 
                            $pAttr = ProductAttribute::create($t_attribute);   
                            $product_attribute_id = $pAttr->product_attribute_id;
                            if($defaultAttribute >= 1) {
                                self::updateDefaultAttribute($product_id,$product_attribute_id);
                            }
                            $selected_attributes = [];
                            foreach ($t_attribute_val_records as $t_variant){                        
                                $t_data = [
                                    'attribute_id' => $t_variant->attribute_id,
                                    'attribute_values_id' => $t_variant->attribute_values_id,
                                    'product_attribute_id' => $product_attribute_id,
                                    'product_id' => $product_id,
                                ];
                                DB::table('product_variations')-> insert($t_data);
                                

                                if ( array_key_exists($t_data['attribute_id'], $selected_attributes) === FALSE ) {
                                    $selected_attributes[$t_data['attribute_id']] = [];
                                }
                                if (! in_array($t_data['attribute_values_id'], $selected_attributes[$t_data['attribute_id']]) ) {
                                    $selected_attributes[$t_data['attribute_id']][] = $t_data['attribute_values_id'];
                                }
                            }
                            foreach ($selected_attributes as $t_attr_id => $t_attr_data) {
                                foreach ($t_attr_data as $t_attr_val_id) {
                                    $t_data = [
                                        'attribute_id' => $t_attr_id,
                                        'attribute_values_id' => $t_attr_val_id,
                                        'product_id' => $product_id,
                                    ];
                                    DB::table('product_selected_attributes')-> insert($t_data);
                                    
                                }
                            }
                        }
                }
            }

            DB::commit();
            return 1;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            DB::rollback();
            return 0;
        }
    }

    public static function get_products($filter=[]){

        $products = DB::table("product")
            ->where($filter)
            ->join("product_category","product_category.product_id","=","product.id")
            ->join("oodle_category","oodle_category.id","=","product_category.category_id")
            ->distinct('product.id')
            ->get(['product.*','product.name as product_name','product.id as product_id','product.created_on as created_at'])->toArray();
        return $products;
        // $products =  ProductModel::distinct()
        // //->join("product_category","product_category.product_id","=","product.id")
        // //->join("oodle_category","oodle_category.id","=","product_category.category_id")
        // ->where($filter)
        // ->get(['product.*','product.name as product_name','product.id as product_id','product.created_on as created_at'])->toArray();
        // return $products;
    }
    public static function search_products($filter=[],$sort='newest',$countries=[],$params=[]){
        switch($sort){
            case('newest'): 
                $sort_key = 'product.id';
                $sort_order = 'desc';
                break;
            case ('oldest'): 
                $sort_key = 'product.id';
                $sort_order = 'asc';
                break;
            default:
                $sort_key = 'product.id';
                $sort_order = 'DESC';
        }
        $products = DB::table("product")
            ->join("product_category","product_category.product_id","=","product.id")
            ->join("oodle_category","oodle_category.id","=","product_category.category_id")
            ->join('users','product.product_vender_id','=','users.id')
            ->join("product_selected_attribute_list","product_selected_attribute_list.product_id","=","product.id")
            ->distinct('product.id')
            ->orderBy($sort_key,$sort_order)
            ->select('product.*','oodle_category.name as category_name','product.product_name as product_name','product.id as product_id','product.created_at as created_at','users.display_name as seller_name','users.business_name as seller_business_name','users.user_image as seller_image','product_category.category_id','product_selected_attribute_list.stock_quantity','product_selected_attribute_list.sale_price','product_selected_attribute_list.regular_price','product_selected_attribute_list.image',
                DB::raw("(SELECT COUNT(p.id) from product p where p.product_vender_id=users.id ) as posting_count")
            );
            $products->where(function ($query) use ($filter,$countries,$params) {
                $query->where($filter);
                if($countries){
                    $query->whereIn('users.country_id',$countries);
                }
                if(!empty($params)){
                    if(isset($params['search_key']) && $params['search_key'] != ''){
                        $query->where(function($query) use($params) {
                            foreach (['product.product_name','users.display_name'] as $t_like_field) {
                                $query->orWhere("{$t_like_field}",'ilike',"%".$params['search_key']."%");
                            }
                        });
                    }
                    if(isset($params['category_ids']) && !empty($params['category_ids'])){
                        $query->whereIn('oodle_category.id',$params['category_ids']);
                    }
                }
            });
        
                
        return $products;
        // $products =  ProductModel::distinct()
        // //->join("product_category","product_category.product_id","=","product.id")
        // //->join("oodle_category","oodle_category.id","=","product_category.category_id")
        // ->where($filter)
        // ->get(['product.*','product.name as product_name','product.id as product_id','product.created_on as created_at'])->toArray();
        // return $products;
    }
    public static function search_services($filter=[],$sort='newest',$countries=[],$params=[]){
        $filter = ['services.service_status'=>1,'services.deleted'=>0];
        switch($sort){
            case('newest'): 
                $sort_key = 'services.service_id';
                $sort_order = 'desc';
                break;
            case ('oldest'): 
                $sort_key = 'services.service_id';
                $sort_order = 'asc';
                break;
            default:
                $sort_key = 'services.service_id';
                $sort_order = 'DESC';
        }
        $products = DB::table("services")
            ->join("service_category","service_category.id","=","services.service_category")
            ->join('users','services.service_vendor_user_id','=','users.id')
            ->distinct('services.service_id')
            ->orderBy($sort_key,$sort_order)
            ->select('services.*','service_category.name as category_name','users.display_name as seller_name','users.business_name as seller_business_name','users.user_image as seller_image','service_category.id as category_id');
            $products->where(function ($query) use ($filter,$countries,$params) {
                $query->where($filter);
                if($countries){
                    $query->whereIn('users.country_id',$countries);
                }
                if(!empty($params)){
                    if(isset($params['search_key']) && $params['search_key'] != ''){
                        $query->where(function($query) use($params) {
                            foreach (['services.service_name','users.display_name'] as $t_like_field) {
                                $query->orWhere("{$t_like_field}",'ilike',"%".$params['search_key']."%");
                            }
                        });
                    }
                    if(isset($params['category_ids']) && !empty($params['category_ids'])){
                        $query->whereIn('oodle_category.id',$params['category_ids']);
                    }
                }
            });
        
                
        return $products;
    }
    public static function search_events($filter=[],$sort='newest',$countries=[],$params=[]){
        $filter = ['event.status'=>1,'event.deleted'=>0];
        switch($sort){
            case('newest'): 
                $sort_key = 'event.event_id';
                $sort_order = 'desc';
                break;
            case ('oldest'): 
                $sort_key = 'event.event_id';
                $sort_order = 'asc';
                break;
            default:
                $sort_key = 'event.event_id';
                $sort_order = 'DESC';
        }
        $products = DB::table("event")
            ->join("event_selected_category","event_selected_category.event_id","=","event.event_id")
            ->join('event_category','event_category.id','=','event_selected_category.event_category_id')
            ->join('users','event.vendor_id','=','users.id')
            ->distinct('event.event_id')
            ->orderBy($sort_key,$sort_order)
            ->select('event.*','event_category.name as category_name','users.display_name as seller_name','users.business_name as seller_business_name','users.user_image as seller_image','event_category.id as category_id');
            $products->where(function ($query) use ($filter,$countries,$params) {
                $query->where($filter);
                if($countries){
                    $query->whereIn('users.country_id',$countries);
                }
                if(!empty($params)){
                    if(isset($params['search_key']) && $params['search_key'] != ''){
                        $query->where(function($query) use($params) {
                            foreach (['event.event_title','users.display_name'] as $t_like_field) {
                                $query->orWhere("{$t_like_field}",'ilike',"%".$params['search_key']."%");
                            }
                        });
                    }
                    if(isset($params['category_ids']) && !empty($params['category_ids'])){
                        $query->whereIn('oodle_category.id',$params['category_ids']);
                    }
                }
            });
        
                
        return $products;
    }
    public static function search_packages($filter=[],$sort='newest',$countries=[],$params=[]){
        $filter = ['packages.active'=>1,'packages.deleted'=>0];
        switch($sort){
            case('newest'): 
                $sort_key = 'packages.id';
                $sort_order = 'desc';
                break;
            case ('oldest'): 
                $sort_key = 'packages.id';
                $sort_order = 'asc';
                break;
            default:
                $sort_key = 'packages.id';
                $sort_order = 'DESC';
        }
        $products = DB::table("packages")
            ->join("package_categories","package_categories.id","=","packages.package_cat")
            ->join('users','packages.vendor_id','=','users.id')
            ->distinct('packages.id')
            ->orderBy($sort_key,$sort_order)
            ->select('packages.*','package_categories.category_name as category_name','users.display_name as seller_name','users.business_name as seller_business_name','users.user_image as seller_image','package_categories.id as category_id');
            $products->where(function ($query) use ($filter,$countries,$params) {
                $query->where($filter);
                if($countries){
                    $query->whereIn('users.country_id',$countries);
                }
                if(!empty($params)){
                    if(isset($params['search_key']) && $params['search_key'] != ''){
                        $query->where(function($query) use($params) {
                            foreach (['packages.title','users.display_name'] as $t_like_field) {
                                $query->orWhere("{$t_like_field}",'ilike',"%".$params['search_key']."%");
                            }
                        });
                    }
                    if(isset($params['category_ids']) && !empty($params['category_ids'])){
                        $query->whereIn('oodle_category.id',$params['category_ids']);
                    }
                }
            });
        
                
        return $products;
    }

    public static function get_product_categories($product_id=0,$request=''){
        if($request=='request'){
            return DB::table("product_request_category")->where('product_id','=',$product_id)->get()->toArray();
        }
        return DB::table("product_category")->where('product_id','=',$product_id)->get()->toArray();
    }
    
    public static function get_product_specs($product_id=0,$request=''){
        if($request=='request'){
            return DB::table("product_request_specs")->select('prod_spec_id','product_id','spec_title','spec_descp','lang')->where('product_id','=',$product_id)->get()->toArray();
        }
        return DB::table("product_specifications")->select('prod_spec_id','product_id','spec_title','spec_descp','lang')->where('product_id','=',$product_id)->get()->toArray();
    }

    public function getImagePathAttribute($value){
        if($value){
            return get_uploaded_image_url(config('global.upload_path').config('global.product_image_upload_dir').$value);//$this->path.$value;
        }else{
            return $this->path.'placeholder.png';
        }
        
    }

    // public function getImageAttribute($value){
    //     if($value){
    //         return get_uploaded_image_url(config('global.upload_path').config('global.product_image_upload_dir').$value);//$this->path.$value;
    //     }else{
    //         return $this->path.'placeholder.png';
    //     }
        
    // }

    public static function get_products_list($where=[],$params=[],$sortby,$sort_order){
        
        $products = ProductModel::leftjoin("users","users.id","=","product.product_vender_id")
            ->where($where)
            ->select('product.*', 'vendor_details.company_name as name','product_selected_attribute_list.image','product_selected_attribute_list.stock_quantity')->orderBy($sortby,$sort_order)
            ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
            ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_id','product.id')
            ;
            
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $search_key = $params['search_key'];
                $products->whereRaw("(product.product_name ilike '%$search_key%' OR users.business_name ilike '%$search_key%')");
                // $products->orWhere('users.display_name','ilike','%'.$params['search_key'].'%');
            }

            if (isset($params['vendor']) && $params['vendor'] != '') {
                
                $products->where('product.product_vender_id',$params['vendor']);
            }
            if (isset($params['store']) && $params['store'] != '') {
                
                $products->where('product.store_id',$params['store']);
            }
            if (isset($params['from']) && $params['from'] != '') {
                $from = gmdate(date('Y-m-d 00:00:00',strtotime($params['from'])));
                $products->where('product.created_at','>=', $from);
            }
            if (isset($params['to']) && $params['to'] != '') {
                $to = gmdate(date('Y-m-d 23:59:59',strtotime($params['to'])));
                $products->where('product.created_at','<=', $to);
            }
            if (isset($params['category']) && $params['category'] != '') {
                $cat_id = $params['category'];

                $products->whereIn('product.id',function($query) use ($cat_id){
                    $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
                });
            }
            if (isset($params['featured'])) {
                
                $products->where('product.featured',$params['featured']);
            }
            if (isset($params['activity_id']) && $params['activity_id'] != '') {
                
                $products->where('product.activity_id',$params['activity_id']);
            }
             if (isset($params['activity_ids']) && $params['activity_ids'] != '') {
                $activity_ids = explode(',', $params['activity_ids']);
                $products->whereIn('product.activity_id',$activity_ids);
            }

            
        }
        //$products->distinct('product.id');
        $products->groupBy(
            [
                'product.id',
                'vendor_details.company_name',
                'product_selected_attribute_list.image',
                'product_selected_attribute_list.stock_quantity',
                'product.product_type',
                'product.product_desc_full',
                'product.product_desc_short',
                'product.product_sale_from',
                'product.product_sale_to',
                'product.product_featured_image',
                'product.product_tag',
                'product.product_created_by',
                'product.created_at',
                'product.product_updated_by',
                'product.updated_at',
                'product.product_status',
                'product.product_deleted',
                'product.product_name',
                'product.product_variation_type',
                'product.product_taxable',
                'product.product_vender_id',
                'product.product_image',
                'product.product_unique_iden',
                'product.product_brand_id',
                'product.product_name_arabic',
                'product.product_desc_full_arabic',
                'product.product_desc_short_arabic',
                'product.cash_points',
                'product.offer_enabled',
                'product.deal_enabled',
                'is_today_offer',
                'product.today_offer_date',
                'product.thanku_perc',
                'product.custom_status',
                'product.meta_title',
                'product.meta_keyword',
                'product.meta_description',
                'product.product_vendor_status',
                'product.product_gender',
                'product.is_kandora',
                'product.collection_id',
                'product.hot_offer_enabled',
                'product.trending_enabled',
                'product.offers_list',
                'product.zero_quantity_orders',
                'product.product_code',
                'product.product_tags',
                'product.sort_order',
                'product.offer_for_short',
                'product.hot_offer_sort_order',
                'product.new_trending_sort_order',
                'product.author_id',
                'product.deleted',
                'product.default_category_id',
                'product.default_attribute_id']);

       // $products->distinct('product.product_name');

        $products->distinct($sortby);
        return $products;
    }
    public static function get_products_list_out_of_stock($where=[],$params=[],$sortby,$sort_order){
        
        $search_key = $params['search_key'];
        $products = ProductModel::leftjoin("users","users.id","=","product.product_vender_id")
            ->where($where)
            ->where('product_selected_attribute_list.stock_quantity', '<=', 10)
            ->select('product.*', 'users.name','product_selected_attribute_list.image','product_selected_attribute_list.stock_quantity')->orderBy($sortby,$sort_order)
            ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_id','product.id')
            ;
            
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $products->whereRaw("(product.product_name ilike '%$search_key%' OR users.business_name ilike '%$search_key%')");
                // $products->orWhere('users.display_name','ilike','%'.$params['search_key'].'%');
            }

            if (isset($params['vendor']) && $params['vendor'] != '') {
                
                $products->where('product.product_vender_id',$params['vendor']);
            }
            if (isset($params['store']) && $params['store'] != '') {
                
                $products->where('product.store_id',$params['store']);
            }
            if (isset($params['from']) && $params['from'] != '') {
                $from = gmdate(date('Y-m-d 00:00:00',strtotime($params['from'])));
                $products->where('product.created_at','>=', $from);
            }
            if (isset($params['to']) && $params['to'] != '') {
                $to = gmdate(date('Y-m-d 23:59:59',strtotime($params['to'])));
                $products->where('product.created_at','<=', $to);
            }
            if (isset($params['category']) && $params['category'] != '') {
                $cat_id = $params['category'];

                $products->whereIn('product.id',function($query) use ($cat_id){
                    $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
                });
            }
        }
        //$products->distinct('product.id');
        $products->groupBy(
            [
                'product.id',
                'users.name',
                'product_selected_attribute_list.image',
                'product_selected_attribute_list.stock_quantity',
                'product.product_type',
                'product.product_desc_full',
                'product.product_desc_short',
                'product.product_sale_from',
                'product.product_sale_to',
                'product.product_featured_image',
                'product.product_tag',
                'product.product_created_by',
                'product.created_at',
                'product.product_updated_by',
                'product.updated_at',
                'product.product_status',
                'product.product_deleted',
                'product.product_name',
                'product.product_variation_type',
                'product.product_taxable',
                'product.product_vender_id',
                'product.product_image',
                'product.product_unique_iden',
                'product.product_brand_id',
                'product.product_name_arabic',
                'product.product_desc_full_arabic',
                'product.product_desc_short_arabic',
                'product.cash_points',
                'product.offer_enabled',
                'product.deal_enabled',
                'is_today_offer',
                'product.today_offer_date',
                'product.thanku_perc',
                'product.custom_status',
                'product.meta_title',
                'product.meta_keyword',
                'product.meta_description',
                'product.product_vendor_status',
                'product.product_gender',
                'product.is_kandora',
                'product.collection_id',
                'product.hot_offer_enabled',
                'product.trending_enabled',
                'product.offers_list',
                'product.zero_quantity_orders',
                'product.product_code',
                'product.product_tags',
                'product.sort_order',
                'product.offer_for_short',
                'product.hot_offer_sort_order',
                'product.new_trending_sort_order',
                'product.author_id',
                'product.deleted',
                'product.default_category_id',
                'product.default_attribute_id']);

       // $products->distinct('product.product_name');

        $products->distinct($sortby);
        return $products;
    }
    public function product_categories()
    {
        return $this->hasMany('App\ProductCategoryModel','product_id','id')->join('oodle_category', 'oodle_category.id', '=', 'product_category.category_id');
    }

    public static function get_product_list($filter=[]){

        $products = DB::table("product")
            ->where($filter)
            ->join("product_category","product_category.product_id","=","product.id")
            ->join("oodle_category","oodle_category.id","=","product_category.category_id")
            ->distinct('product.id')
            ->select('product.*','product.name as product_name','product.id as product_id','product.created_on as created_at');
        return $products;
    }
    public static function get_products_list_api($where=[],$params=[],$sortby,$sort_order){
        
        $search_key = $params['search_key'];
        $products = ProductModel::leftjoin("users","users.id","=","product.product_vender_id")
            ->where($where)
            ->select('product.*', 'display_name','business_name','product_selected_attribute_list.*','product_category.category_id','oodle_category.name as category_name','users.business_name as vendorname','users.user_image as vendorimage')->orderBy($sortby,$sort_order)
            ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','product.default_attribute_id')
            ->leftjoin('product_category','product_category.product_id','product.id')
            ->leftjoin('oodle_category','oodle_category.id','product_category.category_id')
            ;
            
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $products->whereRaw("(product.product_name ilike '%$search_key%' OR users.business_name ilike '%$search_key%')");
                // $products->orWhere('users.display_name','ilike','%'.$params['search_key'].'%');
            }

            if (isset($params['product_vender_id']) && $params['product_vender_id'] != '') {
                
                $products->where('product.product_vender_id',$params['product_vender_id']);
            }
            if (isset($params['from']) && $params['from'] != '') {
                $from = gmdate(date('Y-m-d 00:00:00',strtotime($params['from'])));
                $products->where('product.created_at','>=', $from);
            }
            if (isset($params['to']) && $params['to'] != '') {
                $to = gmdate(date('Y-m-d 23:59:59',strtotime($params['to'])));
                $products->where('product.created_at','<=', $to);
            }
            if (isset($params['category']) && $params['category'] != '') {
                $cat_id = $params['category'];

                $products->whereIn('product_category.category_id',explode(",",$cat_id));
                
            }
        }
        $products->distinct('product.id');
        $products->get(); 
        return $products;
    }
    public static function get_products_details($sortby,$sort_order,$where=[],$params=[]){
        
        $search_key = '';

        $products = ProductModel::leftjoin("users","users.id","=","product.product_vender_id")
            ->where($where)
            ->select('product.*', 'display_name','business_name','product_selected_attribute_list.*','users.business_name as vendorname','users.user_image as vendorimage')->orderBy($sortby,$sort_order)
            ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','product.default_attribute_id','users.business_name','users.id as vendor_id');
            
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $products->whereRaw("(product.product_name ilike '%$search_key%' OR users.business_name ilike '%$search_key%')");
                // $products->orWhere('users.display_name','ilike','%'.$params['search_key'].'%');
            }

            if (isset($params['product_vender_id']) && $params['product_vender_id'] != '') {
                
                $products->where('product.product_vender_id',$params['product_vender_id']);
            }
            if (isset($params['from']) && $params['from'] != '') {
                $from = gmdate(date('Y-m-d 00:00:00',strtotime($params['from'])));
                $products->where('product.created_at','>=', $from);
            }
            if (isset($params['to']) && $params['to'] != '') {
                $to = gmdate(date('Y-m-d 23:59:59',strtotime($params['to'])));
                $products->where('product.created_at','<=', $to);
            }
            if (isset($params['category']) && $params['category'] != '') {
                $cat_id = $params['category'];

                $products->whereIn('product.id',function($query) use ($cat_id){
                    $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
                });
            }
        }
        $products->distinct('product.id');
        $products->get(); 
        return $products;
    }
    public static function sellers_by_categories($cat)
    {
        // $parent_categories = DB::table('oodle_category')->select('parent_id')->whereIn('id', $cat)->get()->toArray();
        $parent_categories = [];
        foreach($cat as $key=>$val){
            $res = DB::table('oodle_category')->select('parent_id')->where('id', $val)->first();
            $parent_categories[$key] = $res->parent_id ? $res->parent_id : $val;
        }
        $cat_ids = $parent_categories;
        $sellers = Users::select('users.id', 'display_name','business_name')->join('users_groups', 'users_groups.user_id', '=', 'users.id')
            ->join('res_groups', 'res_groups.id', '=', 'users_groups.group_id')->where('res_groups.name', 'seller')->orderBy('display_name', 'asc');
        if ($cat_ids) {
            $sellers->whereIn('users.id', function ($query) use ($cat_ids) {
                $query->select('user_id')->groupBy('user_id')->from('oodle_user_categories')->whereIn("category_id", $cat_ids);
            });
        }
        return $sellers;
    }
    public static function update_product_request($product_id,$ins=[],$category_ids=[],$specs=[],$spec_doc_ins=[]){
        DB::beginTransaction();

        try {
            DB::table('product_request')->where('id',$product_id)-> update($ins);

            if(!empty($category_ids)){
                DB::table('product_request_category')->where('product_id', '=', $product_id)->delete();
                foreach($category_ids as $cat){
                    DB::table('product_request_category')-> insertGetId(['category_id'=>$cat,'product_id'=>$product_id]);
                }
            }
            DB::table('product_request_specs')->where('product_id', '=', $product_id)->delete();
            if(!empty($specs)){
                foreach($specs as  $d => $v){ 
                    DB::table('product_request_specs')-> insertGetId(['title'=>$v['title'],'description'=>$v['description'],'product_id'=>$product_id]);
                }
            }
            if(!empty($spec_doc_ins)){
                foreach($spec_doc_ins as  $d){ 
                    DB::table('product_request_docs')-> insertGetId(['title'=>$d['title'],'doc_path'=>$d['doc_path'],'product_id'=>$product_id]);
                }
            }

            DB::commit();
            return 1;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            DB::rollback();
            return 0;
        }
    }
    public static function save_product_request($ins=[],$category_ids=[],$specs=[],$spec_doc_ins=[]){
        DB::beginTransaction();

        try {
            $product_id = DB::table('product_request')-> insertGetId($ins);

            if(!empty($category_ids)){
                foreach($category_ids as $cat){
                    DB::table('product_request_category')-> insertGetId(['category_id'=>$cat,'product_id'=>$product_id]);
                }
            }
            if(!empty($specs)){
                foreach($specs as  $d => $v){ 
                    DB::table('product_request_specs')-> insertGetId(['title'=>$v['title'],'description'=>$v['description'],'product_id'=>$product_id]);
                }
            }
            if(!empty($spec_doc_ins)){
                foreach($spec_doc_ins as  $d){ 
                    DB::table('product_request_docs')-> insertGetId(['title'=>$d['title'],'doc_path'=>$d['doc_path'],'product_id'=>$product_id]);
                }
            }

            DB::commit();
            return $product_id;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            DB::rollback();
            return 0;
        }
    }
    public static function get_product_request_list($where=[],$params=[]){
        $search_key = $params['search_key'];
        $products = DB::table('product_request')->leftjoin("users","users.id","=","product_request.product_vender_id")
            ->where($where)
            ->select('product_request.*', 'display_name','business_name')->orderBy('product_request.created_on','desc');
            
        if( !empty($params) ){
            if(isset($params['search_key']) && $params['search_key'] != ''){
                $products->whereRaw("(product_request.name ilike '%$search_key%' OR users.business_name ilike '%$search_key%')");
                // $products->orWhere('users.display_name','ilike','%'.$params['search_key'].'%');
            }
            if (isset($params['from']) && $params['from'] != '') {
                $from = gmdate(date('Y-m-d 00:00:00',strtotime($params['from'])));
                $products->where('product_request.created_on','>=', $from);
            }
            if (isset($params['to']) && $params['to'] != '') {
                $to = gmdate(date('Y-m-d 23:59:59',strtotime($params['to'])));
                $products->where('product_request.created_on','<=', $to);
            }
            if (isset($params['category']) && $params['category'] != '') {
                $cat_id = $params['category'];

                $products->whereIn('product_request.id',function($query) use ($cat_id){
                    $query->select('product_id')->from('product_request_category')->where("category_id","=",$cat_id);
                });
            }
        }
        return $products;
    }

    public static function getProductAttributes()
    {
       $data =  Attribute::join('product_attribute_values','product_attribute_values.attribute_id','product_attribute.attribute_id')->where(['attribute_status'=>1,'product_attribute.is_deleted'=>0,'product_attribute_values.is_deleted'=>0])->get();
       return $data ;
    }

     public static function getAttributeValuesByIds( $ids )
    {
        $ids = (array)$ids;
                  
       return DB::table('product_attribute')->select('product_attribute_values.*', 'product_attribute.attribute_id', 'product_attribute.attribute_name', 'product_attribute.attribute_name_arabic')->join('product_attribute_values','product_attribute_values.attribute_id','product_attribute.attribute_id')->whereIn('product_attribute_values.attribute_values_id', $ids)->get();

        
    }


    public static function getCategoriesCondition($data)
    {
        return DB::table('category')->orderBy('name','asc')->get()->toArray();           
    }

    public static function addProductByVendor($data)
    {   
        if(isset($data['product']) && !empty($data['product'])) {
           
            if ( isset($data['product']['product_unique_iden']) &&  $data['product']['product_unique_iden'] < 0 ) {                
                $count = 1;
                while ( $count != 0 ) {                    
                    $product_uid = (string)random_int(1000, 999999999);
                    $detail =  DB::table('product')->where('product_unique_iden',$product_uid)->get();
                    $count = count($detail);
                }
                $data['product']['product_unique_iden'] = $product_uid;   
                $product_id =  DB::table('product')-> insertGetId($data['product']); 
                if($product_id > 0 ) { 
                    //product category
                    if ( isset($data['product_category']) && !empty($data['product_category']) ) {
                        $category_data = [];  
                        foreach ($data['product_category'] as $key => $t_row) {
                            $category_data[] = array('product_id'=>$product_id,'category_id'=>$t_row);
                           
                                               
                        }  
                        if (! empty($category_data) ) {
                            DB::table('product_category')-> insert($category_data);
                          
                        }
                    }

                    ProductCuisines::where('product_id',$product_id)->delete();
                    if ( isset($data['cuisines']) && !empty($data['cuisines']) ) {
                        foreach ($data['cuisines'] as $key => $row) {
                            $Cuisine = new ProductCuisines();
                            $Cuisine->product_id = $product_id;
                            $Cuisine->cuisine_id = $row;
                            $Cuisine->save();
                        }
                    }

                    // Specifications
                    if ( isset($data['specifications']) && !empty($data['specifications']) ) {
                        foreach ($data['specifications'] as $t_spec) {
                            $specifications[] = 
                                array('product_id'=>$product_id,'spec_title'=>$t_spec['title'],
                                    'spec_descp' => $t_spec['description'],

                                    );
                            
                            
                            
                            }
                        DB::table('product_specifications')-> insert($specifications);
                    }
                    if ( isset($data['product_simple_variant']) && !empty($data['product_simple_variant']) ) {  
                        $checkExist = DB::table('product_selected_attribute_list')->where('product_id',$product_id)->get();
                        if(!empty(count($checkExist))) {
                            DB::table('product_selected_attribute_list')->where('product_id',$product_id)-> update($data['product_simple_variant']);
                        } else {
                            $data['product_simple_variant']['product_id'] = $product_id;
                            /*DB::table('product_selected_attribute_list')-> insert($data['product_simple_variant']);*/
                            $pAttr = ProductAttribute::create($data['product_simple_variant']);
                            $product_attribute_id = $pAttr->product_attribute_id;
                            self::updateDefaultAttribute($product_id,$product_attribute_id);
                            
                        }

                    }
                    if ( isset($data['product_multi_variant']) && !empty($data['product_multi_variant']) ) {
                        foreach ($data['product_multi_variant'] as $t_attribute) {
                             $t_attribute_values = [];
                            if ( isset($t_attribute['attribute_values']) ) {
                                $t_attribute_values = $t_attribute['attribute_values'];
                                unset($t_attribute['attribute_values']);
                            }

                            if ( empty($t_attribute_values) ) {
                                continue;
                            }
                            $t_attribute_val_records = self::getAttributeValuesByIds($t_attribute_values);
                            if ( empty($t_attribute_val_records) ) {
                               continue;
                            }
                            $t_attribute['product_id'] = $product_id;

                            
                            $default_attribute = $t_attribute['default_attribute_id'];
                            unset($t_attribute['default_attribute_id']) ;
                            $pAttr = ProductAttribute::create($t_attribute);
                            $product_attribute_id = $pAttr->product_attribute_id;
                            $default_attribute = $product_attribute_id;//edited
                            if($default_attribute >=1) {
                                self::updateDefaultAttribute($product_id,$product_attribute_id);
                            }
                            $selected_attributes = [];
                            foreach ($t_attribute_val_records as $t_variant){                        
                                $t_data = [
                                    'attribute_id' => $t_variant->attribute_id,
                                    'attribute_values_id' => $t_variant->attribute_values_id,
                                    'product_attribute_id' => $product_attribute_id,
                                    'product_id' => $product_id,
                                ];
                                DB::table('product_variations')->insert($t_data);
                                

                                if ( array_key_exists($t_data['attribute_id'], $selected_attributes) === FALSE ) {
                                    $selected_attributes[$t_data['attribute_id']] = [];
                                }
                                if (! in_array($t_data['attribute_values_id'], $selected_attributes[$t_data['attribute_id']]) ) {
                                    $selected_attributes[$t_data['attribute_id']][] = $t_data['attribute_values_id'];
                                }
                            }
                            foreach ($selected_attributes as $t_attr_id => $t_attr_data) {
                                foreach ($t_attr_data as $t_attr_val_id) {
                                    $t_data = [
                                        'attribute_id' => $t_attr_id,
                                        'attribute_values_id' => $t_attr_val_id,
                                        'product_id' => $product_id,
                                    ];
                                    DB::table('product_selected_attributes')-> insert($t_data);
                                    
                                }
                            }
                        }
                    }

                    return 1;
                } else {
                    return 0;
                }
                
            }
        }
    }


    public static function getProductVariationAttributes( $attribute_id )
    {
        return DB::table('product_variations')->select('attribute_values_id', 'product_variations_id', 'attribute_id')->where('product_id', $attribute_id)
                        ->orderBy('attribute_id','asc')
                        ->get()
                        ->toArray();
    }
      public static function getProductVariationAttributesList( $attribute_id )
    { 
        return DB::table('product_variations')->select('attribute_values_id', 'product_variations_id', 'attribute_id')->where('product_attribute_id', $attribute_id)
                        ->orderBy('attribute_id','asc')
                        ->get()
                        ->toArray();
    }
     public static  function getProductVariants( $product_id, $attribute_id=NULL )
    {
        
        $query = DB::table('product_selected_attribute_list')
                        ->where('product_id', $product_id);
        if ( $attribute_id ) {
            $query = $query->where('product_attribute_id', $attribute_id);
        }
        return $query = $query->orderBy('product_attribute_id', 'asc')
                        ->get()->toArray();       
        
    }

    public static function getProductInfo($productid)
    {
        return DB::table('product')->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_id','product.id')->where('product.id',$productid)->get();
    }

    public static function removeVariationAttribute($product_id, $attr_val_id )
    {
        $result = DB::table('product_variations')
        ->leftjoin('product_selected_attribute_list',
         'product_variations.product_attribute_id',
         'product_selected_attribute_list.product_attribute_id')
        ->where('product_variations.product_id', $product_id)
        ->where('product_variations.attribute_values_id', $attr_val_id)
        ->get()->toArray();
        if (! empty($result) )  {
           
            $attribute_ids = array_column($result, 'product_attribute_id');   
            DB::table('product_selected_attribute_list')->whereIn('product_attribute_id',$attribute_ids)->delete();

            DB::table('product_variations')->whereIn('product_attribute_id',$attribute_ids)->delete();

            DB::table('product_selected_attributes')->whereIn('attribute_values_id',$attribute_ids)->where('product_id',$product_id)->delete();
            return 1;
        }
        return 0;
    }

    public static function updateDefaultAttribute($product_id,$product_attribute_id) {
        
        $up = ['default_attribute_id'=>$product_attribute_id];
        DB::table('product')->where('id',$product_id)->update($up);
        return true;
    }
    
    public static function getProductDetails($product_id ,$attribute_id)
    {
       return DB::table('product as P')->select('P.*','PA.stock_quantity','PA.sale_price','PA.regular_price')->leftjoin('product_selected_attribute_list as PA','PA.product_id','P.id')->where('P.id',$product_id)->where('PA.product_id',$product_id)->where('PA.product_attribute_id',$attribute_id)->where('product_status',1)->where('deleted',0)->get()->toArray();
    }
    public static function get_data_ratings($where){
        $data = DB::table('ratings')
                    ->select('ratings.*','users.first_name','users.last_name')
                    ->where($where)
                    ->leftjoin('users','users.id','ratings.user_id')
                    ->get()
                    ->toArray();
                    return $data;
    }
    public static function get_data_total_ratings($where)
    {
        $rating_avg = 0;
        $rating_sum = DB::table('ratings')
                    ->where($where)
                    ->sum('rating');

        $rating_count = DB::table('ratings')
                    ->where($where)
                    ->count('rating');
        if($rating_sum != 0 && $rating_count != 0)
        {
          $rating_avg =  $rating_sum/$rating_count; 
        }
                    return number_format($rating_avg, 2, '.', '');

    }
    
    public static function exportDetails($category_id = [],$vendor_id = 0 ){

        $return= ProductModel::select('product_name','category.name as category_name','users.name as vendor_name','product.created_at','stock_quantity','sale_price','regular_price')
        ->leftJoin('category','category.id','=','product.default_category_id')
        ->leftJoin('users','users.id','=','product.product_vender_id')
        ->leftJoin('product_selected_attribute_list','product_selected_attribute_list.product_id','=','product.id');
        $return=$return->where('product_deleted',0);
        if(session("user_type")=="S"){
             $return=$return->where('product.product_vender_id',session("user_id"));
        }
        if($vendor_id > 0){
            $return=$return->where('product.product_vender_id',$vendor_id);
        }
        if(!empty($category_id))
        {
           $return=$return->whereIn('product.default_category_id',$category_id);
        }
        if($vendor_id > 0){
            $return=$return->where('product.product_vender_id',$vendor_id);
        }
        return $return->orderBy('product.id','desc')->get();
        
    }
    public static function stock_reduce($pid,$qty)
    { 
         DB::table('product_selected_attribute_list')
                ->where('product_selected_attribute_list.product_id',$pid)
                ->update([
                'product_selected_attribute_list.stock_quantity'=> DB::raw('product_selected_attribute_list.stock_quantity-'.$qty) 
                ]);
               
               
    }
    public static function ticket_reduce($pid,$qty)
    { 
         DB::table('event')
                ->where('event.event_id',$pid)
                ->update([
                'ticket_count'=> DB::raw('ticket_count-'.$qty) 
                ]);
               
               
    }

    public static function products_list($where = [], $filter = [], $limit = '', $offset = 0)
    {

        $products = ProductModel::where($where)->select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended');

        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $products->whereRaw("(product_name ilike '%$srch%')");
        }
        if(isset($filter['category_id']) && $filter['category_id'] !=''){
            $products->whereRaw("product.id in (select product_id from product_category where category_id = '".$filter['category_id']."' or category_id in (select id from category where parent_id='".$filter['category_id']."')) ");
        }
        if (isset($filter['vendor_id']) && $filter['vendor_id']) {
           
            $products->where('product_vender_id', $filter['vendor_id']);
        }
        if (isset($filter['master_product_id']) && $filter['master_product_id']) {
           
            $products->where('master_product', $filter['master_product_id']);
        }
        if (isset($filter['featured']) && $filter['featured'] == 1) {
            
            $products->where('product.featured',1);
        }
        if (isset($filter['recommended']) && $filter['recommended'] == 1) {
            
            $products->where('product.recommended',1);
        }
        if (isset($filter['activity_id']) && $filter['activity_id'] != '') {
            
            $products->where('product.activity_id',$filter['activity_id']);
        }

        // dd($filter['category_ids']);
        if (isset($filter['category_ids']) && $filter['category_ids'] != '') {
            $cat_id = explode(',', $filter['category_ids']);
            $products->join("product_category","product_category.product_id","=","product.id");
            $products->whereIn("product_category.category_id",$cat_id);
            // $products->whereIn('product.id',function($query) use ($cat_id){
            //     $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
            // });
        }
        
        if (isset($filter['start_price']) && $filter['start_price'] != '') {
            $start_price = $filter['start_price'];

            $products->whereIn('product.id',function($query) use ($start_price){
                $query->select('product_id')->from('product_selected_attribute_list')->where("sale_price",">=",$start_price);
            });
        }
        if (isset($filter['end_price']) && $filter['end_price'] != '') {
            $end_price = $filter['end_price'];
            $products->whereIn('product.id',function($query) use ($end_price){
                $query->select('product_id')->from('product_selected_attribute_list')->where("sale_price","<=",$end_price);
            });
        }
        if (isset($filter['ratting']) && $filter['ratting'] != '') {
            $ratting = explode(',', $filter['ratting']);
            $products->whereIn("rating_avg",$ratting);//->whereIn('product.id',function($query) use ($ratting){
                // $query->select('product_id')->from('ratings')->whereIn("rating",$ratting);
            // });
        }
        if (isset($filter['cuisines_ids']) && $filter['cuisines_ids'] != '') {
            $cuisine_id = explode(',', $filter['cuisines_ids']);
            // $products->join("product_cuisines","product_cuisines.product_id","=","product.id");
            // $products->whereIn("product_cuisines.cuisine_id",$cuisine_id);
            $products->whereHas('product_cuisine',function($c_q) use($cuisine_id){
                return $c_q->whereIn("cuisine_id",$cuisine_id);
            });
            // $products->has('product_cuisine','>','0');
        }

        if($limit !="") {
            $products->limit($limit)->skip($offset);
        }
        $products->orderBy('created_at','desc');
        return $products;
        
    }

    public static function products_list_ratings($where = [], $filter = [], $limit = '', $offset = 0)
    {

        $products = DB::table('product')->where($where)->select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id','master_product','product.featured','product.recommended');

        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $products->whereRaw("(product_name ilike '%$srch%')");
        }
        if(isset($filter['category_id']) && $filter['category_id'] !=''){
            $products->whereRaw("product.id in (select product_id from product_category where category_id = '".$filter['category_id']."' or category_id in (select id from category where parent_id='".$filter['category_id']."')) ");
        }
        if (isset($filter['vendor_id']) && $filter['vendor_id']) {
           
            $products->where('product_vender_id', $filter['vendor_id']);
        }
        if (isset($filter['master_product_id']) && $filter['master_product_id']) {
           
            $products->where('master_product', $filter['master_product_id']);
        }
        if (isset($filter['featured']) && $filter['featured'] == 1) {
            
            $products->where('product.featured',1);
        }
        if (isset($filter['recommended']) && $filter['recommended'] == 1) {
            
            $products->where('product.recommended',1);
        }
        if (isset($filter['activity_id']) && $filter['activity_id'] != '') {
            
            $products->where('product.activity_id',$filter['activity_id']);
        }

        // dd($filter['category_ids']);
        if (isset($filter['category_ids']) && $filter['category_ids'] != '') {
            $cat_id = explode(',', $filter['category_ids']);
            $products->join("product_category","product_category.product_id","=","product.id");
            $products->whereIn("product_category.category_id",$cat_id);
            // $products->whereIn('product.id',function($query) use ($cat_id){
            //     $query->select('product_id')->from('product_category')->where("category_id","=",$cat_id);
            // });
        }
        if (isset($filter['start_price']) && $filter['start_price'] != '') {
            $start_price = $filter['start_price'];

            $products->whereIn('product.id',function($query) use ($start_price){
                $query->select('product_id')->from('product_selected_attribute_list')->where("sale_price",">=",$start_price);
            });
        }
        if (isset($filter['end_price']) && $filter['end_price'] != '') {
            $end_price = $filter['end_price'];
            $products->whereIn('product.id',function($query) use ($end_price){
                $query->select('product_id')->from('product_selected_attribute_list')->where("sale_price","<=",$end_price);
            });
        }
        if (isset($filter['ratting']) && $filter['ratting'] != '') {
            $ratting = explode(',', $filter['ratting']);
            $products->whereIn("rating_avg",$ratting);//->whereIn('product.id',function($query) use ($ratting){
                // $query->select('product_id')->from('ratings')->whereIn("rating",$ratting);
            // });
        }

        if($limit !="") {
            $products->limit($limit)->skip($offset);
        }
        $products->orderBy('product.created_at','desc');
        return $products;
        
    }


    // public static function  product_details($where){
    //     $products = ProductModel::leftjoin("users","users.id","=","product.product_vender_id")
    //     ->where($where)
    //     ->select('product.*', 'display_name','business_name','product_selected_attribute_list.*','product_category.category_id','category.name as category_name','users.business_name as vendorname','users.user_image as vendorimage')
    //     ->leftjoin('product_selected_attribute_list','product_selected_attribute_list.product_attribute_id','product.default_attribute_id')
    //     ->leftjoin('product_category','product_category.product_id','product.id')
    //     ->leftjoin('category','category.id','product_category.category_id')
    //     ;
        
    // $products->distinct('product.id');
    // $products =  $products->first(); 
   
    //     return $products;
    // }
    public static function getProductVariant($product_id, $product_attribute_id = null)
    {
        $where['product.deleted'] = 0;
        $where['product.product_status'] = 1;
        $where['product.id'] = $product_id;
       

        $res = ProductModel::select('product.*', 'product_category.category_id AS category_id', 'category.name', 'product_selected_attribute_list.*','brand.name as brand')->leftjoin('product_category', 'product_category.product_id', 'product.id')->leftjoin('category', 'category.id', 'product_category.category_id')->where('product_category.category_id', '!=', null)->leftjoin('product_selected_attribute_list', 'product_selected_attribute_list.product_id', 'product.id')->leftjoin('brand','brand.id','=','product.product_brand_id')
        ->with('vendor.vendordata');
        if ($product_attribute_id) {
            $res->where('product_selected_attribute_list.product_attribute_id', $product_attribute_id);
        }
        $res = $res->where($where)->orderBy('product_selected_attribute_list.product_attribute_id', 'DESC')->first();

        return [1, $res, ''];

        // Join Product Attribute
        // $this->db->select(
        //     '
        //     ,
        //     product_selected_attribute_list.rated_users,
        //     seller.seller_details_id AS store_id,
        //     seller.store_name,
        //     seller.seller_logo AS store_logo,
        //     seller.delivery_time_min,
        //     seller.delivery_time_max,
        //     seller.rated_users as vendor_rated_users,
        //     seller.rating as vendor_rating'
        // );
        // // Arabic
        // if ( $this->lang_code == 2 ) {
        //     $this->db->select('seller.store_name_arabic AS store_name');
        // }

        // Seller Info
        // $this->db->join('user_table user', 'product.product_vender_id = user.user_id', 'left');
        // $this->db->join('seller_details seller', 'user.user_id = seller.user_id', 'left');

        // $this->db->join('(SELECT * FROM brand WHERE brand_language_code = '. $this->db->escape($this->lang_code) .') brand', 'product.product_brand_id = brand.brand_id', 'left');

    }

    public static function getProductVariantAttributes( $variant_id )
    {        

        $res = DB::table('product_variations')->select('product_variations.attribute_id','product_variations.attribute_values_id')->join('product_attribute','product_variations.attribute_id','product_attribute.attribute_id')->where('product_variations.product_attribute_id', $variant_id)->where('product_attribute.attribute_status', 1)->where('product_attribute.is_deleted', 0)->get();
        //printr($res->toArray());
        $attributes = [];
        foreach ($res as $row) {
            $attributes[$row->attribute_id] = $row->attribute_values_id;
        }
        return $attributes;
    }
    public static function getSelectedProductAttributeVals( $variant_id )
    {
        $res = DB::table('product_variations')->select('product_variations.product_variations_id','product_variations.product_id','product_variations.product_attribute_id','product_attribute.attribute_id','product_attribute_values.attribute_values_id','attribute_type.attribute_type_uid AS attribute_type','product_attribute.attribute_name','product_attribute_values.attribute_values','product_attribute_values.attribute_value_in','product_attribute_values.attribute_color','product_attribute_values.attribute_value_label')->join('product_attribute','product_variations.attribute_id','product_attribute.attribute_id')->join('product_attribute_values','product_attribute_values.attribute_values_id','product_variations.attribute_values_id')->leftjoin('attribute_type','product_attribute.attribute_type','attribute_type.id')->where('product_variations.product_attribute_id',$variant_id)->where('product_attribute.attribute_status',1)->where('product_attribute.is_deleted',0)->where('product_attribute_values.is_deleted', 0)->groupby(['product_variations.product_id', 'product_variations.product_attribute_id', 'product_variations.product_variations_id', 'product_variations.product_attribute_id', 'product_attribute.attribute_id', 'product_attribute.attribute_name', 'product_attribute_values.attribute_values_id', 'attribute_type.attribute_type_uid'])->orderby('attribute_value_sort_order','asc')->first();
       return $res;
    }
    public static function getSelectedProductAttributeValsFull( $variant_id )
    {
        $res = DB::table('product_variations')
        ->select('product_variations.product_variations_id','product_variations.product_id','product_variations.product_attribute_id','product_attribute.attribute_id','product_attribute_values.attribute_values_id','attribute_type.attribute_type_uid AS attribute_type','product_attribute.attribute_name','product_attribute_values.attribute_values','product_attribute_values.attribute_value_in','product_attribute_values.attribute_color','product_attribute_values.attribute_value_label')
        ->join('product_attribute','product_variations.attribute_id','product_attribute.attribute_id')
        ->join('product_attribute_values','product_attribute_values.attribute_values_id','product_variations.attribute_values_id')
        ->leftjoin('attribute_type','product_attribute.attribute_type','attribute_type.id')->where('product_variations.product_attribute_id',$variant_id)
        ->where('product_attribute.attribute_status',1)->where('product_attribute.is_deleted',0)->where('product_attribute_values.is_deleted', 0)->groupby(['product_variations.product_id', 'product_variations.product_attribute_id', 'product_variations.product_variations_id', 'product_variations.product_attribute_id', 'product_attribute.attribute_id', 'product_attribute.attribute_name', 'product_attribute_values.attribute_values_id', 'attribute_type.attribute_type_uid'])->orderby('attribute_value_sort_order','asc')->get();
       return $res;
    }


    public static function getProductAttributeVals(array $product_ids=array() )
    {
        if (! empty($product_ids) ) {
            $res = DB::table('product_variations')
            ->select('product_variations.product_variations_id','product_variations.product_id','product_variations.product_attribute_id','product_attribute.attribute_id','product_attribute_values.attribute_values_id','attribute_type.attribute_type_uid AS attribute_type','product_attribute.attribute_name','product_attribute_values.attribute_values','product_attribute_values.attribute_value_in','product_attribute_values.attribute_color','product_attribute_values.attribute_value_label')
            ->join('product_attribute','product_variations.attribute_id','product_attribute.attribute_id')
            ->join('product_attribute_values','product_attribute_values.attribute_values_id','product_variations.attribute_values_id')
            ->leftjoin('attribute_type','product_attribute.attribute_type','attribute_type.id')
            ->wherein('product_variations.product_id',$product_ids)
            ->where('product_attribute.attribute_status',1)
            ->where('product_attribute.is_deleted',0)
            ->where('product_attribute_values.is_deleted', 0)
            ->groupby(['product_variations.product_id', 'product_variations.product_attribute_id', 'product_variations.product_variations_id', 'product_variations.product_attribute_id', 'product_attribute.attribute_id', 'product_attribute.attribute_name', 'product_attribute_values.attribute_values_id', 'attribute_type.attribute_type_uid'])
            ->orderby('attribute_value_sort_order','asc')->get();
           return $res;
        }
        return [];
    }
    public static function get_product_attribute_id_from_attributes($attributes=[],$product_id=0){
        
    	if (! empty($attributes) ) {

            
            $where1 = "product_id=$product_id and";
            $where2 = '';
            foreach ($attributes as $attribute_id => $attribute_values_id) {
                if(!$where2){
                    $where2.= "(attribute_id=$attribute_id and attribute_values_id=$attribute_values_id)";
                }else{
                    $where2.= " or(attribute_id=$attribute_id and attribute_values_id=$attribute_values_id)";
                }
            }
            $where = $where1."(".$where2.")";
            $res = DB::table('product_variations')->select('product_attribute_id')
            ->whereRaw($where)->groupby(['product_attribute_id'])->having(DB::raw('count(product_attribute_id)'), count($attributes))->get()->toArray();

            if ( count($res) == 0 ) {
                return [FALSE, 0, 'Product variant not found.'];
            } else if ( count($res) > 1 ) {
                return [FALSE, 0, 'Unable to proceed. Multiple variants found.'];
            }
            
            
            $product_attribute_id = $res[0]->product_attribute_id;
            return [TRUE,$product_attribute_id,''];
        }else{
        	return [2,0,''];
        }
    }
    public static function moda_products_list($where = [], $filter = [], $limit = '', $offset = 0)
    {
        $products = DB::table('product')->where($where)->select('product.id', 'product.product_name', 'product.product_type', 'default_attribute_id')->join('moda_sub_categories','moda_sub_categories.id','product.moda_sub_category');

        if (isset($filter['search_text']) && $filter['search_text']) {
            $srch = $filter['search_text'];
            $products->whereRaw("(product_name ilike '%$srch%')");
        }
        if (isset($filter['store_id']) && $filter['store_id']) {
           
            $products->where('store_id', $filter['store_id']);
        }
        if($limit !="") {
            $products->limit($limit)->skip($offset);
        }

        if (isset($filter['sort_by_newest']) && $filter['sort_by_newest']) {
            $sort_by_newest = $filter['sort_by_newest'];
            if($sort_by_newest=='asc' || $sort_by_newest=='desc'){
                $products->orderBy('product.created_at',$sort_by_newest);
            }
        }else{
            $products->orderBy('product.created_at','desc');
        }
        
        return $products;
        
    }
}
