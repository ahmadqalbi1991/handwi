<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\Categories;
use App\Models\VendorModel;
use App\Models\ProductMasterModel;
use App\Models\Stores;
use App\CustomRequestModel;
use App\RequestModel;
use App\Models\ProductDocsModel;
use App\Models\Brands;
use Validator;
use DB;
use App\Models\ProductAttribute;
use App\Models\Common;
use App\Traits\StoreImageTrait;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Imports\ProductImport;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use App\Models\Cuisine;
use App\Models\ProductCuisines;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use StoreImageTrait;
    const PRODUCT_TYPE_SIMPLE = 1;
    const PRODUCT_TYPE_VARIABLE = 2;
    const ATTRIBUTE_COL_START = 26;
    public function index()
    {
        if(Auth::user()->activity_id != 7 && Auth::user()->activity_id != 5 && Auth::user()->activity_id != 3)
        {
            return redirect('portal');
        }
        $page_heading = "Products";

        $is_food  = '';
        $p_name  = 'Product';
        $page_heading  = 'Products';
        if(auth()->user()->activity_id == '5'){
            $p_name  = 'Dish';
            $page_heading  = 'Dishes';
            $is_food  = '1';
        }

        $filter = ['product.deleted'=>0, 'users.id' => auth()->user()->id];
        $params = [];
        $category_ids = [];
        $sr_key=$_GET['search_key'] ?? '';
        $sr_key = str_replace("'","",$sr_key);
        $params['search_key'] = $sr_key;
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $to = isset($_GET['to']) ? $_GET['to'] : '';
        $params['from'] = $from;
        $params['to'] = $to;
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $params['category'] = $category;
        if($category){
            $category_ids[0] = $category;
        }

        $search_key = $params['search_key'];

        $sortby = "product.id";
        $sort_order = "desc";
        if(isset($_GET['sort_type']) && $_GET['sort_type'] !="") {
            if($_GET['sort_type'] ==1) {
                $sortby = "product.product_name";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==2) {
                $sortby = "product.product_name";
                $sort_order = "desc";
            } else if($_GET['sort_type'] ==3) {
                $sortby = "product.id";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==4) {
                $sortby = "product.id";
                $sort_order = "desc";
            } else if($_GET['sort_type'] ==5) {
                $sortby = "product.updated_at";
                $sort_order = "asc";
            } else if($_GET['sort_type'] ==6) {
                $sortby = "product.updated_at";
                $sort_order = "desc";
            }
        }
        $list = ProductModel::get_products_list($filter, $params,$sortby,$sort_order)->paginate(10);

        // $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
        // $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0]);
        $parent_categories_list->where('activity_id',auth()->user()->activity_id);
        $parent_categories_list =  $parent_categories =  $parent_categories_list->get()->toArray();



        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;


        return view("portal.product.list", compact("page_heading", "list","search_key",'category_list','sub_category_list','from','to','category_ids','category','p_name','is_food'));

    }
    public function delete_product($id = '')
    {
        $check = Common::check_already('order_products',['product_id'=>$id,'product_type'=>'1']);
        $status = 0;
        if($check != 1)
        {
            ProductModel::where('id', $id)->update(['deleted' => 1]);
            $status = "1";
            $message = "Record removed successfully";
        }
        else
        {
            $message = "Unable to delete child details exist";
        }

        echo json_encode(['status' => $status, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = "")
    { 
        
        $brand   = Brands::where(['brand.deleted' => 0])->where('activity_id',auth()->user()->activity_id)->get();
        //$product_master = ProductMasterModel::where(['deleted'=>0,'active'=>1])->get();
        //
        // $page_heading = "Create Products";


        $p_name  = 'Create Product';
        $is_food  = '';
        $page_heading  = 'Create Products';
        if(auth()->user()->activity_id == '5'){
            $p_name  = 'Create Dish';
            $page_heading  = 'Create Dishes';
            $is_food  = '1';

        }

        $mode         = "Create";
        $category_ids = [];
        $action = "add"; 
        $active       = '';
        $specs        = [];
        $name         = '';
        $master_product = '';
        $seller_user_id         = '';
        $store_id               = '';
        $description  = '';
        $image_path   = '';
        $docs=[];
        $sku = "";
        $meta_title = "";
        $meta_keyword = "";
        $meta_description = "";
        $product_type = 1;
        $attribute_list = ProductModel::getProductAttributes();
        $regular_price = '';
        $pr_code = '';
        $sale_price = '';
        $readonly =  FALSE;
        $stock_quantity = '';
        $input_index = 1;
        $product_desc = "";
        $t_variant_allow_backorder  = "";
        $taxable  = "";
        $default_category_id = "";
        $default_attribute_id = "";
        $size_chart = "";
        $sellers = VendorModel::select('users.id', 'vendor_details.company_name as name')->where(['role'=>'3','users.verified'=>'1','users.deleted'=>'0','users.deleted'=>'0'])
        ->leftjoin('vendor_details','vendor_details.user_id','=','users.id')
        ->get();
        //$sellers = [];

        // $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();

        $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0]);
        $parent_categories->where('activity_id',auth()->user()->activity_id);
        $parent_categories_list =  $parent_categories = $parent_categories->get()->toArray();


        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->where('activity_id',auth()->user()->activity_id)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
        $combinations = [];
        $selected_attributes = [];
        $product_variations = [];
        $attribute_value_ids = [];
        $attribute_values = [];
        $stores           = [];
        $product          = [];
        $product_desc_full = "";
        $bar_code = "";
        $product_code = "";

        $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
        $Cuisines_ids = [];
        
        $moda_sub_categories = [];
        if($id !="" && $id >0 ) { 
            $product = ProductModel::getProductInfo($id); //print_r($product); die();
            $action = "edit";
            if($product){
                $product = $product[0];
                $docs = ProductDocsModel::where('product_id',$id)->get()->toArray();
                $page_heading = "Edit Product";

                $p_name  = 'Edit Product';
                $page_heading  = 'Edit Product';
                if(auth()->user()->activity_id == '5'){
                    $p_name  = 'Edit Dish';
                    $page_heading  = 'Edit Dish';
                }


                $mode         = "edit";
                $category_ids = [];
                //$master_product = $product->master_product;
                $readonly = ($product->product_status == 1 ) ? TRUE : FALSE;
                $product_type = $product->product_type;
                $sku = $product->product_unique_iden;
                $meta_title     = $product->meta_title;
                $meta_keyword   = $product->meta_keyword;
                $meta_description = $product->meta_description;
                $active       = $product->product_status;
                $name         = $product->product_name;
                $description  = $product->product_desc_full;
                $image_path          = "";
                $seller_user_id      = $product->product_vender_id;
                $store_id            = $product->store_id;
                $selected_attributes = [];
                $regular_price =$product->regular_price;
                $size_chart =$product->size_chart;
                $pr_code = $product->pr_code;;
                $t_variant_allow_backorder =$product->allow_back_order;
                $taxable =$product->taxable;
                $sale_price = $product->sale_price;
                $stock_quantity = $product->stock_quantity;
                $product_desc = $product->product_desc_short;
                $product_desc_full = $product->product_full_descr;
                $bar_code = $product->barcode;
                $product_code = $product->pr_code;
                $default_category_id = $product->default_category_id;
                $default_attribute_id = $product->default_attribute_id;
                $product_categories = ProductModel::get_product_categories($id);
                $category_ids       = array_column($product_categories,'category_id');
                $specs              = ProductModel::get_product_specs($id);
                $attribute_value_ids = [];  
                $product_variant_attributes = ProductModel::getProductVariationAttributes($id);
                $stores       = Stores::select('id','store_name')->where(['deleted' => 0,'active'=>1,'vendor_id'=>$seller_user_id])->get();
                $Cuisines = Cuisine::where(['deleted' => '0','status' => '1'])->orderBy('sort_order', 'asc')->get();
                $Cuisines_ids = ProductCuisines::where('product_id',$id)->pluck('cuisine_id')->toArray();
                
                
                foreach ($product_variant_attributes as $t_row) {
                    if ( array_key_exists($t_row->attribute_id, $selected_attributes) === FALSE ) {
                        $selected_attributes[$t_row->attribute_id] = [];
                    }
                    if (! in_array($t_row->attribute_values_id, $selected_attributes[$t_row->attribute_id]) ) {
                        $selected_attributes[$t_row->attribute_id][] = $t_row->attribute_values_id;
                    }
                    if (! in_array($t_row->attribute_values_id, $attribute_value_ids) ) {
                        $attribute_value_ids[] = $t_row->attribute_values_id;
                    }
                }
                $product_variations =  ProductModel::getProductVariants($id); 

                foreach ($product_variations as $t_variant) {
                    $variantion_attributes = ProductModel::getProductVariationAttributesList($t_variant->product_attribute_id);   
                    foreach ($variantion_attributes as $t_row) {                    
                        if ( array_key_exists($t_row->attribute_id, $selected_attributes) === FALSE ) {
                            $selected_attributes[$t_row->attribute_id] = [];
                        }
                        if (! in_array($t_row->attribute_values_id, $selected_attributes[$t_row->attribute_id]) ) {
                            $selected_attributes[$t_row->attribute_id][] = $t_row->attribute_values_id;
                            
                        }
                        if (! in_array($t_row->attribute_values_id, $attribute_value_ids) ) {
                            $attribute_value_ids[] = $t_row->attribute_values_id;
                        }
                    }   
                    $combinations[] =  array_column($variantion_attributes, 'attribute_values_id');
                }
                
                $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->where('activity_id',auth()->user()->activity_id)->get()->toArray();
                $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->where('activity_id',auth()->user()->activity_id)->get()->toArray();

                $parent_categories = array_column($parent_categories, 'name', 'id');
                asort($parent_categories);
                $category_list = $parent_categories;

                $sub_categories = [];
                foreach ($parent_categories_list as $row) {
                    $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
                }
                $sub_category_list = $sub_categories;
                $attributes = ProductModel:: getProductAttributes();
                $attribute_list = [];
                foreach ($attributes as $row) {
                    if ( !isset($attribute_list[$row->attribute_id]['name']) ) {
                        $attribute_list[$row->attribute_id]['name'] = $row->attribute_name;
                    }
                    $attribute_list[$row->attribute_id]['values'][] = [$row->attribute_values_id, $row->attribute_values];
                }
               $attribute_values = [];
                if ( count($attribute_value_ids) > 0 ) {
                    $result = ProductModel::getAttributeValuesByIds($attribute_value_ids);
                    foreach ($result as $row) {
                        $attribute_values[$row->attribute_values_id] = $row;
                    }
                }


            
            
            }
        }

        
        return view('portal.product.create',compact('page_heading','mode','category_list','sub_category_list','category_ids','id','active','name','description','image_path','specs','sellers','seller_user_id','docs','sku','meta_title','meta_keyword','meta_description','product_type','combinations','product','action','attribute_list','size_chart','selected_attributes','product_variations','attribute_value_ids','attribute_values','regular_price','readonly','sale_price','stock_quantity','input_index','t_variant_allow_backorder','taxable','product_desc','product_desc_full','bar_code','product_code','default_category_id','default_attribute_id','brand','pr_code','store_id','stores','p_name','is_food','Cuisines','Cuisines_ids'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add_product(Request $request)
    {
      
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $redirectUrl = '';
        $id      = $request->id;

        $rules   = [
            'product_name'      => 'required',
            'category_ids'    => 'required',
            
        ];

        if($id==''){
           // $rules['product_image'] = 'required';
        }

        $validator = Validator::make($request->all(),$rules,
        [
            'category_ids.required' => 'Category required',
            'product_image.image' => 'should be in image format (.jpg,.jpeg,.png)'
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }else{
            $input = $request->all();

            $specs = $request->spec;

            $category_ids=$request->category_ids;

            if($id) {
                $productDetails = ProductModel::getProductInfo($id);
                $productDetails = $productDetails[0];
                $action ="edit";
                $product_type = $productDetails->product_type;
            } else {
                $action = "add";
                $product_type = $request->product_type;
            }

            $ins   = [
                'name'          => $request->product_name,
                'updated_on'    => gmdate('Y-m-d H:i:s'),
                'updated_by'    => session("user_id"),
                'seller_user_id'=> session("user_id"),
                'active'        => $request->active,
                'description'   => $request->description,
                'product_brand_id'=> $request->brand??0
            ];
            
           

            $button_counter = $request->button_counter;
            $spec_doc_ins = [];

            for ($i = 1; $i <= $button_counter; $i++) {
                if ($file = $request->file("spec_doc_image_" . $i)) {
                    $file_name = "";
                    $imageuploaded = $this->verifyAndStoreImage($request,"spec_doc_image_" . $i,config("global.product_image_upload_dir"));
                    if($imageuploaded!="") {
                       $file_name = $imageuploaded; 
                    }
                   
                    $spec_doc_ins[] = ['title' => $request->{"spec_doc_title_$i"}, 'doc_path' => $file_name];
                }
            }
            
            $specifications = [];  
            
            $categories = $request->category_ids; 
          
            if (! empty($categories) ) {
                $all_categories = ProductModel::getCategoriesCondition($categories);
                $all_categories = array_column($all_categories, 'category_parent_id', 'category_id');
                foreach ($categories as $t_cat) {
                    $p_cat_id = $all_categories[$t_cat] ?? 0;
                    do {
                        if ( $p_cat_id > 0) {
                            $categories[] = $p_cat_id;
                            $p_cat_id = $all_categories[$p_cat_id] ?? 0;
                        }
                    } while ( $p_cat_id > 0 );
                }
                $categories = array_filter($categories);
                $categories = array_unique($categories);
            } 

           
            $vendor_id = $request->seller_id;
            $activity_id = auth()->user()->activity_id ?? '';
            $data = [
                'product' => [
                    'product_type' => $product_type,
                    'product_name' => $request->product_name,
                    'product_name_arabic' => $request->product_name_arabic,
                    'product_desc_full' => $request->product_desc ,
                    'product_desc_full_arabic' => $request->product_desc_full_arabic,
                    'product_desc_short' => $request->product_desc,
                    'product_desc_short_arabic' => $request->product_desc_arabic,
                    'product_vender_id' => session("user_id"),
                    'meta_title' => $request->meta_title,
                    'meta_keyword' => $request->meta_keyword,
                    'meta_description'    => $request->meta_description,
                    'default_category_id' => $request->default_category_id,
                    'product_brand_id'    => $request->brand??0,
                    'featured'            => $request->featured ?? 0,
                    'recommended'         => $request->recommended ?? 0,
                    //'master_product'      => $request->product_name ?? 0,
                    'boxcount'            => $request->boxcount ?? " ",
                    'activity_id'=> $activity_id
                ],
                'product_category' => $categories,
                'specifications' => $request->spec,
                'cuisines' => $request->cuisines,
            ];
            if($product_type!=1) {

              //  $data['product']['default_attribute_id'] = $request->default_attribute_selected;
            }

            if ( !$id) {
                $action_date =  date('Y-m-d H:i:s');
                $data['product']['product_vender_id'] = session("user_id");
                $data['product']['product_created_by'] = session("user_id");
                $data['product']['created_at'] = $action_date;
                $data['product']['product_unique_iden'] = -1;
                $data['product']['product_status'] = 0;
                $data['product']['product_vendor_status'] = 0;
                $data['product']['product_deleted'] = 0;
                $data['product']['product_variation_type'] = 1;
                $data['product']['product_taxable'] = 1;
            } else  {

                unset($data['product']['product_type']);
                $data['product']['product_updated_by'] = session("user_id");
                $data['product']['updated_at'] = date('Y-m-d H:i:s');
            }
            $data['product']['product_status'] = $request->active;;
            if ( $product_type == 1 ) {
                // Prepending current images
                if ( $action == 'edit' ) {
                   // array_unshift($imageFiles['simple'], $product->image);
                }  
                $product_simple_image= $request->product_simple_image;


               
                $imagesList = []; 
                if($id) {
                    if(!empty($productDetails->image))
                    {
                    $imagesList = explode(",",$productDetails->image);
                    }
                }
                 for ($i = 0; $i < $request->image_counter; $i++) {
                    if ($file = $request->file("product_simple_image_" . $i)) {
                        $file_name = "";
                        $imageuploaded = $this->verifyAndStoreImage($request,"product_simple_image_" . $i,config("global.product_image_upload_dir"));
                        if($imageuploaded!="") {
                           $file_name = $imageuploaded; 
                        }
                        
                        $imagesList[] = $file_name;
                    }
                }   
                if($request->croped_images){
                    foreach (explode('data:', $request->all()['croped_images']) as $key => $croped_file) {
                        $file_name = "";
                        $imageuploaded = $this->verifyAndStoreImage($request,"croped_images",config("global.product_image_upload_dir"),$croped_file);
                        if($imageuploaded!="") {
                           $file_name = $imageuploaded; 
                            $imagesList[] = $file_name; 
                        }
                    }
                }  
                
                
                if(isset($imageFiles['simple'])) {
                    $data['product']['product_image'] = trim(implode(',', $imageFiles['simple']), ',');
                } 
                
                $size_chart = $request->size_chart_old;
                if($request->file("size_chart")){
                    $response = image_upload($request,'products','size_chart');
                    if($response['status']){
                        $size_chart = $response['link'];
                    }
                } 
                
                $data['product_simple_variant'] = [
                    'regular_price' => $request->regular_price,
                    'sale_price' => $request->sale_price,
                    'stock_quantity' => $request->stock_quantity,  
                    'product_desc' => $request->product_desc,
                    'taxable' => $request->taxablesingle ?? 0,
                    'product_full_descr' => $request->product_full_descr,
                    'barcode' => $request->bar_code,
                    'pr_code'  => $request->product_code,
                    'weight'   => $request->weight,
                    'length'   => $request->length,
                    'height'   => $request->height,
                    'width'    => $request->width,
                    'image'    => implode(",",$imagesList),
                    'size_chart'=> $size_chart
                ]; 

            } else  {

                    $variant_attribute_id = $request->product_variant_attribute_id;
                    $variant_regular_price =  $request->product_variant_regular_price ;
                    $variant_sale_price =  $request->product_variant_sale_price;
                    $variant_stock_quantity =  $request->product_variant_stock_qty;
                    $variant_allow_backorder =   $request->product_variant_allow_backorder;
                    $variant_taxable =   $request->taxable;
                    $variant_attribute_values =  $request->product_variant_attribute_values ;
                    $variant_short_desc =  $request->product_variant_short_desc;
                    $variant_full_desc =  $request->product_variant_full_desc;
                    $variant_barcode =  $request->product_variant_barcode;
                    $variant_product_code =  $request->product_variant_product_code;
                    $variant_weight    =  $request->weight_variant;
                    $variant_length    =  $request->length_variant;
                    $variant_width     =  $request->width_variant;
                    $variant_height    =  $request->height_variant;
                    $default_attribute =  $request->default_attribute_id;
                    $variant_size_chart=  $request->size_chart_attr;
                    $variant_size_chart_old=  $request->size_chart_attr_old;

                    

                    


                
                    $imagesList = [];
                    for($i = 0 ; $i < count($request->product_variant_attribute_id) ;$i++) {
                         $fname = "product_variant_image_" . $i;
                       // print_r($request->$fname);
                        if(1) { 

                            $tname = "image_counter_".$i;
                            for ($k = 0; $k < $request->$tname; $k++) {
                                $tt_name = $fname."_".$k;
                               
                                if ($file = $request->$tt_name) {
                                    $file_name = "";
                                    $imageuploaded = $this->verifyAndStoreImage($request,$tt_name,config("global.product_image_upload_dir"));
                                    if($imageuploaded!="") {
                                       
                                       $imagesList[$i][] = $imageuploaded ;
                                    }
                                }
                            }
                        }
                        if($request->croped_image){
                            // dd(explode('data:', $request->croped_image[$i]));
                            foreach (explode('data:', $request->croped_image[$i]) as $key => $croped_file) {
                                $file_name = "";
                                $imageuploaded = $this->verifyAndStoreImage($request,"croped_image",config("global.product_image_upload_dir"),$croped_file);
                                if($imageuploaded!="") {
                                   $imagesList[$i][] = $imageuploaded ;
                                }
                            }
                        }

                    } 

                    for ( $vi = 0; $vi < count($variant_regular_price); $vi++ ) {

                        //size chart image
                       $size_chart_attr_file = $variant_size_chart_old[$vi];
                       if(!empty($variant_size_chart[$vi]))
                       {
                         $destinationPath = 'uploads/products/';
                         $size_chart_attr = time().$variant_size_chart[$vi]->getClientOriginalName();
                         $variant_size_chart[$vi]->move($destinationPath, $size_chart_attr);
                         $size_chart_attr_file = $destinationPath.$size_chart_attr;
                       }
                       
                        //size chart image END

                        $t_attribute = [
                            'regular_price' => $variant_regular_price[$vi] ?? 0,
                            'sale_price' => $variant_sale_price[$vi] ?? 0,
                            'stock_quantity' => $variant_stock_quantity[$vi] ?? 0,
                            'allow_back_order' => $variant_allow_backorder[$vi] ?? 0,
                            'taxable' => $variant_taxable[$vi] ?? 0,
                            'image' => implode( ',', ($imagesList[$vi] ?? []) ),
                            'attribute_values' => $variant_attribute_values[$vi] ?? [],
                             'product_desc' => $variant_short_desc[$vi] ?? '',
                             'product_full_descr' => $variant_full_desc[$vi] ??'',
                             'barcode' => $variant_barcode[$vi] ?? '',
                             'pr_code' => $variant_product_code[$vi] ?? '',
                             'default_attribute_id' => $default_attribute[$vi] ?? '',
                             'weight'  => $variant_weight[$vi] ?? 0,
                             'length'  => $variant_length[$vi] ?? 0,
                             'width'   => $variant_width[$vi] ?? 0,
                             'height'      => $variant_height[$vi] ?? 0,
                             'size_chart'  => $size_chart_attr_file,
                        ];  

                        
                        if ( $action == 'edit' && !empty($variant_attribute_id) ) {
                            $t_attribute['product_attribute_id'] = $variant_attribute_id[$vi] ?? 0;
                            $t_attribute['default_attribute_id'] = isset($default_attribute[0]) && $default_attribute[0] == $variant_attribute_id[$vi] ? $default_attribute[0] : '';
                            if ( $t_attribute['product_attribute_id'] > 0 ) {
                                $t_variant_row = ProductModel:: getProductVariants($id, $t_attribute['product_attribute_id']);
                                if (! empty($t_variant_row) && !empty($t_attribute['image']) ) {
                                    $t_attribute['image'] = trim(implode(',', ($imagesList[$vi] ?? [])), ',');                                
                                }
                            }
                        }
                        $data['product_multi_variant'][] = $t_attribute;
                    }   


            }
           
            if($id){ 
                $ret = 1;    
                $data['product']['updated_at'] = date('Y-m-d H:i:s');
                $ret = ProductModel::update_product($id,$data['product'],$data['product_category'],$data['specifications'],$spec_doc_ins,$data);
                if($ret){
                    $status = "1";
                    $message= "Record updated successfully";
                }else{
                    $message = "Something went wrong please try again";
                }
            } else{
                $ret = ProductModel::addProductByVendor($data);  
                
                if($ret){
                    $status = "1";
                    $message= "Record saved successfully";
                }else{
                    $message = "Something went wrong please try again";
                }
            }
            
            
        }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $productsall = ProductModel::where(['deleted' => 0])->get();
        //
        $combinations = [];
        $product = ProductModel::find($id);  //print_r($product);
        $brand   = Brand::where(['brand.deleted' => 0])->get();
        $action = "edit";
        if($product){
            $docs = ProductDocsModel::where('product_id',$id)->get()->toArray();
            $page_heading = "Edit Product";
            $mode         = "edit";
            $category_ids = [];
            $readonly = ($product->product_status == 1 ) ? TRUE : FALSE;
            $product_type = $product->product_type;
            $sku = $product->product_unique_iden;
            $meta_title     = $product->meta_title;
            $meta_keyword   = $product->meta_keyword;
            $meta_description = $product->meta_description;
            $active       = $product->active;
            $name         = $product->product_name;
            $description  = $product->product_desc_full;
            $image_path   = $product->image_path;
            $seller_user_id = session("user_id");
            $selected_attributes = [];
            $regular_price =$product->regular_price;

            $pr_code = $product->pr_code;
            $sale_price = $product->sale_price;
            $sellers = Users::select('res_users.id', 'display_name','business_name')->join('res_users_groups', 'res_users_groups.user_id', '=', 'res_users.id')
                ->join('res_groups', 'res_groups.id', '=', 'res_users_groups.group_id')
                ->where('res_groups.name', 'seller')
                ->get();
            $product_categories = ProductModel::get_product_categories($id);
            $category_ids       = array_column($product_categories,'category_id');
            $specs              = ProductModel::get_product_specs($id);
            $attribute_value_ids = [];
            $product_variant_attributes = ProductModel::getProductVariationAttributes($id);

            foreach ($product_variant_attributes as $t_row) {
                if ( array_key_exists($t_row->attribute_id, $selected_attributes) === FALSE ) {
                    $selected_attributes[$t_row->attribute_id] = [];
                }
                if (! in_array($t_row->attribute_values_id, $selected_attributes[$t_row->attribute_id]) ) {
                    $selected_attributes[$t_row->attribute_id][] = $t_row->attribute_values_id;
                }
                if (! in_array($t_row->attribute_values_id, $attribute_value_ids) ) {
                    $attribute_value_ids[] = $t_row->attribute_values_id;
                }
            }
            $product_variations =  ProductModel::getProductVariants($id);

            foreach ($product_variations as $t_variant) {
                $variantion_attributes = ProductModel::getProductVariationAttributesList($t_variant->product_attribute_id);
                foreach ($variantion_attributes as $t_row) {
                    if ( array_key_exists($t_row->attribute_id, $selected_attributes) === FALSE ) {
                        $selected_attributes[$t_row->attribute_id] = [];
                    }
                    if (! in_array($t_row->attribute_values_id, $selected_attributes[$t_row->attribute_id]) ) {
                        $selected_attributes[$t_row->attribute_id][] = $t_row->attribute_values_id;

                    }
                    if (! in_array($t_row->attribute_values_id, $attribute_value_ids) ) {
                        $attribute_value_ids[] = $t_row->attribute_values_id;
                    }
                }
                $combinations[] =  array_column($variantion_attributes, 'attribute_values_id');
            }

            $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
            $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

            $parent_categories = array_column($parent_categories, 'name', 'id');
            asort($parent_categories);
            $category_list = $parent_categories;

            $sub_categories = [];
            foreach ($parent_categories_list as $row) {
                $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
            }
            $sub_category_list = $sub_categories;
            $attributes = ProductModel:: getProductAttributes();
            $attribute_list = [];
            foreach ($attributes as $row) {
                if ( !isset($attribute_list[$row->attribute_id]['name']) ) {
                    $attribute_list[$row->attribute_id]['name'] = $row->attribute_name;
                }
                $attribute_list[$row->attribute_id]['values'][] = [$row->attribute_values_id, $row->attribute_values];
            }
            $attribute_values = [];
            if ( count($attribute_value_ids) > 0 ) {
                $result = ProductModel::getAttributeValuesByIds($attribute_value_ids);
                foreach ($result as $row) {
                    $attribute_values[$row->attribute_values_id] = $row;
                }
            }

            return view('portal.product.create',compact('page_heading','mode','category_list','sub_category_list','category_ids','id','active','name','description','image_path','specs','sellers','seller_user_id','docs','sku','meta_title','meta_keyword','meta_description','product_type','combinations','product','action','attribute_list','selected_attributes','product_variations','attribute_value_ids','attribute_values','regular_price','readonly','sale_price','brand','pr_code','productsall'));
        }else{
            abort(404);
        }
    }
    public function delete_document($id = '')
    {
        ProductDocsModel::where('id', $id)->delete();
        $status = "1";
        $message = "Document removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $prev_data = ProductModel::find($id)->toArray();
        $category = ProductModel::find($id);
        if($category){
            $category->deleted = 1;
            $category->active  = 0;
            $category->updated_on = gmdate('Y-m-d H:i:s');
            $category->updated_by = session("user_id");
            $category->save();
            $status = "1";
            $message = "Product removed successfully";

            //tracking section
            $track_ins = [
                'old_value'         =>  serialize($prev_data),
                'new_value'         =>   '',
                'section_uid'       =>  'category',
                'section'           =>  'Category',
                'action_type'       =>   'delete',
                'description'       =>   'Category Deleted',
                'created_at'        =>   gmdate('Y-m-d H:i:s'),
                'created_uid'       =>   session("user_id")
            ];

        }else{
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status'=>$status,'message'=>$message,'o_data'=>$o_data]);
    }
    function change_status(Request $request){
        $status  = "0";
        $message = "";
        if(ProductModel::where('id',$request->id)->update(['product_status'=>$request->status])){
            $status = "1";
            $msg = "Successfully activated";
            if(!$request->status){
                $msg = "Successfully deactivated";
            }
            $message = $msg;
        }else{
            $message = "Something went wrong";
        }
        echo json_encode(['status'=>$status,'message'=>$message]);
    }
    function sellers_by_categories(Request $request){
        $cat = $request->cat;
        $sellers = [];
        if($cat){
            $sellers = ProductModel::sellers_by_categories($cat)->get();
        }
        echo json_encode(['status'=>"1",'data'=>$sellers]);
    }

    public function products_requests()
    {
        $page_heading = "Product Requests";
        $filter = ['oodle_product_request.status'=>0];
        $params = [];
        $category_ids = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $to = isset($_GET['to']) ? $_GET['to'] : '';
        $params['from'] = $from;
        $params['to'] = $to;
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $params['category'] = $category;
        if($category){
            $category_ids[0] = $category;
        }

        $search_key = $params['search_key'];
        $list = ProductModel::get_product_request_list($filter, $params)->paginate(10);

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;


        return view("portal.product.request_list", compact("page_heading", "list","search_key",'category_list','sub_category_list','from','to','category_ids','category'));

    }
    public function add_to_product($id)
    {
        //
        $product = DB::table('oodle_product_request')->find($id);
        if($product){
            $docs = DB::table('oodle_product_request_docs')->where('product_id',$id)->get();
            // dd($docs);
            $page_heading = "Add To Product";
            $mode         = "Edit";
            $category_ids = [];
            $active       = 0;
            $name         = $product->name;
            $description  = $product->description;
            $image_path   = $product->image_path;
            $seller_user_id = $product->seller_user_id;
            $sellers = Users::select('res_users.id', 'display_name','business_name')->where('id',$product->seller_user_id)
                ->get();

            $product_categories = ProductModel::get_product_categories($id,'request');
            $category_ids       = array_column($product_categories,'category_id');

            $specs              = ProductModel::get_product_specs($id,'request');


            $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
            $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

            $parent_categories = array_column($parent_categories, 'name', 'id');
            asort($parent_categories);
            $category_list = $parent_categories;

            $sub_categories = [];
            foreach ($parent_categories_list as $row) {
                $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
            }
            $sub_category_list = $sub_categories;
            return view('portal.product.add_to_product',compact('page_heading','mode','category_list','sub_category_list','category_ids','id','active','name','description','image_path','specs','sellers','seller_user_id','docs'));
        }else{
            abort(404);
        }
    }
    public function delete_prd_req_doc($id = '')
    {
        DB::table('oodle_product_request_docs')->where('id', $id)->delete();
        $status = "1";
        $message = "Document removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function req_to_prd(Request $request)
    {
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $redirectUrl = '';
        $id      = $request->id;

        $rules   = [
            'seller_id'      => 'required',
            'product_name'      => 'required',
            'category_ids'    => 'required',
            'description'       => 'required'
        ];

        if($id==''){
            // $rules['product_image'] = 'required';
        }

        $validator = Validator::make($request->all(),$rules,
            [
                'seller_id.required' => 'Seller required',
                'category_ids.required' => 'Category required',
                'product_image.image' => 'should be in image format (.jpg,.jpeg,.png)'
            ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }else{
            $input = $request->all();

            $specs = $request->spec;

            $category_ids=$request->category_ids;



            $ins   = [
                'name'          => $request->product_name,
                'updated_on'    => gmdate('Y-m-d H:i:s'),
                'updated_by'    => session("user_id"),
                'seller_user_id'=> $request->seller_id,
                'active'        => $request->active,
                'description'   => $request->description
            ];

            if($file = $request->file("product_image")){
                if(isset($request->cropped_upload_image) && $request->cropped_upload_image){
                    $image_parts = explode(";base64,", $request->cropped_upload_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $imageName = uniqid() .time(). '.'.$image_type;
                    $path = \Storage::disk('s3')->put(config('global.product_image_upload_dir').$imageName, $image_base64);
                    $path = \Storage::disk('s3')->url($path);
                    // $file->storeAs(config('global.product_image_upload_dir').$imageName,$image_base64,'s3');
                    $ins['image_path'] = $imageName;
                }else{
                    $dir = config('global.upload_path')."/".config('global.product_image_upload_dir');
                    $file_name = time().$file->getClientOriginalName();
                    //$file->move($dir,$file_name);
                    $file->storeAs(config('global.product_image_upload_dir'),$file_name,'s3');
                    $ins['image_path'] = $file_name;
                }
            }else{
                $ins['image_path'] = $request->image_path;
            }

            $req_spec_doc_ins = DB::table('oodle_product_request_docs')->select('title','doc_path')->where('product_id',$request->req_id)->get();
            $req_spec_doc_ins = json_decode(json_encode($req_spec_doc_ins), true);
            $button_counter = $request->button_counter;

            $spec_doc_ins = [];

            for ($i = 1; $i <= $button_counter; $i++) {
                if ($file = $request->file("spec_doc_image_" . $i)) {
                    $dir = config('global.upload_path') . "/" . config('global.product_image_upload_dir');

                    $file_name = time() . $file->getClientOriginalName();
                    // $file->move($dir, $file_name);
                    $file->storeAs(config('global.product_image_upload_dir'),$file_name,'s3');
                    $spec_doc_ins[] = ['title' => $request->{"spec_doc_title_$i"}, 'doc_path' => $file_name];
                }
            }
            $spec_doc_ins = array_merge($req_spec_doc_ins,$spec_doc_ins);

            $ins['created_on'] = gmdate('Y-m-d H:i:s');
            $ins['created_by'] = session("user_id");
            $ret = ProductModel::save_product($ins,$category_ids,$specs,$spec_doc_ins);
            if($ret){
                DB::table('oodle_product_request')->where('id',$request->req_id)->update(['status'=>1]);
                $status = "1";
                $message= "Request successfully added to product";
            }else{
                $message = "Something went wrong please try again";
            }


        }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
    }

    public function loadProductAttribute(Request $request)
    {

        $product_type = $request->product_type;
        if($request->action == 'Create' )
            $action       = 'add';
        else
            $action = 'edit';

        if ( $product_type == 2 ) {
            $attributes = ProductModel::getProductAttributes();
            $attribute_list = [];  //print_r($attributes);
            foreach ($attributes as $row) {
                $row = (object)$row ;
                if ( !isset($attribute_list[$row->attribute_id]['name']) ) {
                    $attribute_list[$row->attribute_id]['name'] = $row->attribute_name;
                }
                $attribute_list[$row->attribute_id]['values'][] = [$row->attribute_values_id, $row->attribute_values];
            }
            $html = view('portal.product.category_attribute_ajax_list',compact('attribute_list', 'action'))->render();


        } else {
            $html = '';
        }
        echo json_encode(['html'=>$html]);


    }

    public function loadProductVariations(Request $request)
    {

        $attributes = $request->product_attribute;
        $attribute_value_ids = [];
        foreach ($attributes as $attr_id => $attr_values) {
            foreach ($attr_values as $attr_val_id) {
                $attribute_value_ids[] = $attr_val_id;
            }
        }

        $attribute_values = [];
        if ( count($attribute_value_ids) > 0 ) {
            $result = ProductModel::getAttributeValuesByIds($attribute_value_ids);
            foreach ($result as $row) {
                $attribute_values[$row->attribute_values_id] = $row;
            }
        }
        $combinations = array_combination(array_values($attributes));
        //echo view('admin/product/product_variant_form', compact('combinations', 'attribute_values'));
        echo json_encode([
            'total_variants' => count($combinations),
            'html' =>  view('portal/product/product_variant_form', compact('combinations', 'attribute_values'))->render()
        ]);
        //$this->render('json');

    }

    public function linkNewAttrForProduct(Request $request)
    {

        $status = 1;
        $message = '';
        $id = $request->product_id;
        $attr_val_id = $request->attr_val_id;
        $product_attributes = $request->product_attribute;
        $start_index = $request->start_index;
        if ( !empty($id) && !empty($attr_val_id) ) {
            $product = ProductModel::find($id);
            if (! empty(($product)) ) {
                $status = 1;
                $attribute_values = [];
                if (! empty($product_attributes) ) {
                    $attribute_values = array_values($product_attributes);
                }
                $attribute_values[] = [$attr_val_id];
                $combinations = array_combination($attribute_values);

                $attribute_value_ids = [];
                foreach ($attribute_values as $k) {
                    foreach ($k as $v) {
                        $attribute_value_ids[] = $v;
                    }
                }
                $attribute_value_ids[] = $attr_val_id;

                $attribute_values = [];
                if ( count($attribute_value_ids) > 0 ) {
                    $result = ProductModel::getAttributeValuesByIds($attribute_value_ids);
                    foreach ($result as $row) {
                        $attribute_values[$row->attribute_values_id] = $row;
                    }
                }
                $include_wrapper = FALSE;
                if (! $start_index ) {
                    $start_index = 0;
                }

                echo json_encode([
                    'total_variants' => count($combinations) + $start_index,
                    'status' =>  $status,
                    'html' => view('portal/product/product_variant_form', compact('combinations', 'attribute_values', 'include_wrapper', 'start_index'))->render()
                ]);
            }
        }



    }


    public function unlinkAttrFromProduct(Request $request)
    {
        if ( 1) {
            $status = 1;
            $message = '';
            $id = $request->product_id;
            $attr_val_id = $request->attr_val_id;

            if ( !empty($attr_val_id) && !empty($id) ) {
                $product = ProductModel::find($id);
                if (! empty($product) ) {
                    //$order_count = $this->M_product->productAttrHasOrders($id, $attr_val_id);
                    $order_count = 0;
                    if ( $order_count > 0 ) {
                        $status = 0;
                        $message = 'Oops! it seems like there are orders placed for variants.';
                    } else {
                        $status = ProductModel::removeVariationAttribute($id, $attr_val_id);
                    }
                }
            }

            echo json_encode(compact('status', 'message'));

        }
    }

    public function removeProductImage(Request $request)
    {
        $status = 0;
        $message = "";
        $product_id = (int)$request->product_id;
        $variant_id = (int)$request->variant_id;
        $product_type = (int)$request->product_type;
        $image = (string)$request->image;
        if($product_id !="" && $variant_id !=""  && $product_type !="" && $image !="" ) {
            $product = ProductModel::getProductVariants($product_id,$variant_id );
            if(!empty($product)) {
                $product = array_shift($product);
                $imageArr = explode(",",$product->image);
                $arValues  = array_values($imageArr);
                $k = array_search($image, $imageArr);

                unset($imageArr[$k])  ;
                $imageArr = array_unique(array_filter($imageArr));
                $newimage = implode(",",$imageArr);
                ProductAttribute::where('product_id',$product_id)->where('product_attribute_id',$variant_id)->update(['image'=>$newimage]);
                $status = 1;
                $message = "Deleted ";
            }
        }
        echo json_encode(compact('status', 'message'));
    }

    public function import_export(){
        $page_heading = "Product Import/Export";
        $filter = ['res_groups.name' => 'seller','res_users.deleted'=>0,'res_users.active'=>1];
        $vendors = Users::get_users_list($filter, [])->get();

        $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
        $category_ids = [];
        return view("portal.product.import", compact("page_heading","vendors","category_list","sub_category_list","category_ids"));
    }

    public function export_submit(REQUEST $request){
        $status  = "0";
        $message = "";
        $isValid = TRUE;
        $errors  = [];

        if ( $isValid ) {


            $validator = Validator::make($request->all(), [
                    'company_id' => 'required'
                ]
            );
            if ($validator->fails()) {
                $status = "0";
                $message = "Validation error occured";
                $errors = $validator->messages();
            } else {
                $company_id     = $request->company_id;

                $is_blank       = $request->is_blank;
                $category_ids   = $request->category_ids;

                $form_data = compact('company_id', 'is_blank', 'category_ids');
                \Session::put('export_form_data', $form_data);

                $status = 1;
            }
        }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
    }
    public function download_import_compatible_xls()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');
        $attr_col_start = self::ATTRIBUTE_COL_START;
        ob_start();

        $form_data      = \Session::get('export_form_data');
        $company_id     = $form_data['company_id'] ?? '';

        $is_blank       = $form_data['is_blank'] ?? 0;
        $category_ids   = $form_data['category_ids'] ?? [];




        $spreadsheet                = new Spreadsheet();
        $objWorkSheetProduct        = new Worksheet($spreadsheet, 'Products');
        $objWorkSheetCategory       = new Worksheet($spreadsheet, 'Categories');
        $objWorkSheetBrand          = new Worksheet($spreadsheet, 'Brand');
        $objWorkSheetForm           = new Worksheet($spreadsheet, 'Form');
        $objWorkSheetAttrVal        = new Worksheet($spreadsheet, 'Attribute');

        $objWorkSheetProduct->setCellValue('A1', 'ID');
        $objWorkSheetProduct->setCellValue('B1', 'Name *');
        $objWorkSheetProduct->setCellValue('C1', 'Description');
        $objWorkSheetProduct->setCellValue('D1', 'Product Type');
        //$objWorkSheetProduct->setCellValue('E1', 'Product Commission');
        $objWorkSheetProduct->setCellValue('E1', 'Category *');
        $objWorkSheetProduct->setCellValue('G1', 'Brand *');
        $objWorkSheetProduct->setCellValue('H1', 'Regular Price');
        $objWorkSheetProduct->setCellValue('I1', 'Sale Price *');
        $objWorkSheetProduct->setCellValue('J1', 'Stock *');
        $objWorkSheetProduct->setCellValue('K1', 'SKU');
        $objWorkSheetProduct->setCellValue('L1', 'UPC code (barcode)');
        $objWorkSheetProduct->setCellValue('M1', 'Weight (kg)');
        $objWorkSheetProduct->setCellValue('N1', 'Length');
        $objWorkSheetProduct->setCellValue('O1', 'Width');
        $objWorkSheetProduct->setCellValue('P1', 'Height');
        $objWorkSheetProduct->setCellValue('Q1', 'Buy 1 Get 1');
        $objWorkSheetProduct->setCellValue('R1', 'Buy 2 Get 1');
        $objWorkSheetProduct->setCellValue('S1', 'Free Shipping');
        $objWorkSheetProduct->setCellValue('T1', 'Allow Review');
        $objWorkSheetProduct->setCellValue('U1', 'Require Review Moderation');
        $objWorkSheetProduct->setCellValue('V1', 'Enable Cart Comment');
        $objWorkSheetProduct->setCellValue('W1', 'Youtube Link');
        $objWorkSheetProduct->setCellValue('X1', 'Image');
        $objWorkSheetProduct->setCellValue('Y1', 'Image Action');


        $objWorkSheetCategory->setCellValue("A1", "Category");
        $objWorkSheetCategory->setCellValue("B1", "ID");

        $objWorkSheetBrand->setCellValue("A1", "Brand");
        $objWorkSheetBrand->setCellValue("B1", "ID");

        $objWorkSheetForm->setCellValue("A1", "Key");
        $objWorkSheetForm->setCellValue("B1", "Value");

        $spreadsheet->addSheet($objWorkSheetProduct, 0);
        $spreadsheet->addSheet($objWorkSheetCategory, 1);
        $spreadsheet->addSheet($objWorkSheetBrand, 2);
        $spreadsheet->addSheet($objWorkSheetForm, 3);
        $spreadsheet->addSheet($objWorkSheetAttrVal, 4);

        $objWorkSheetCategory->getStyle('A1:B1')->getFont()->setBold(true);
        $objWorkSheetBrand->getStyle('A1:B1')->getFont()->setBold(true);
        $objWorkSheetForm->getStyle('A1:B1')->getFont()->setBold(true);

        $this->load->model('category_model');
        $this->category_model->where("category.service_id !=",RESTURENT_UNIQUE_ID);
        $this->category_model->where("category.service_id",$store->service_id);
        $all_categories = $res_categories = $this->category_model->only_categories()->result();
        $categories = [];
        foreach ( $res_categories as $row ) {
            $category_tree = [];
            if($row->parent_tree)
            {
                $parent_categories  = array_filter(explode("->",$row->parent_tree));

                foreach($parent_categories as $p_cat)
                {
                    $category2          = $this->ecom_product_model->getCategoryForProduct($p_cat)->row();
                    $category_tree[]    = $category2->name;
                }
            }
            $categories[$row->id] = $row;
            $category_tree[]= $row->name;
            //$category_tree = array_reverse( $category_tree );
            $categories[$row->id]->name = implode(">", $category_tree);
        }

        $this->load->model("brand_model");
        $this->brand_model->where("brand.service_id",$store->service_id);
        $brands_list = $this->brand_model->brands()->result();
        $brands = array_column($brands_list, 'name', 'id');
        asort($brands);


        $this->load->model("attribute_model");
        $this->attribute_model->where("attribute.store_id",$store->id);
        $attributes = $this->attribute_model->categories()->result();
        $attributes = array_column($attributes, 'attribute_name', 'attribute_id');
        asort($attributes);


        $attr_val_list = $this->attribute_model->getAttributeValuesCondition();
        $attribute_values = [];
        foreach ($attr_val_list as $attr_val) {
            $attribute_values[$attr_val->attribute_id][$attr_val->attribute_values_id] = $attr_val->attribute_values;
        }

        foreach ($attribute_values as $attribute_id => $attr_val) {
            ksort($attr_val);
            $attribute_values[$attribute_id] = $attr_val;
        }




        $objWorkSheetCategory->getColumnDimension("B")->setVisible(false);
        $objWorkSheetBrand->getColumnDimension("B")->setVisible(false);

        // Category Sheet
        $category_row = 2;
        foreach ( $categories as $row ) {
            $objWorkSheetCategory->setCellValue("A".$category_row, $row->name.'###'.$row->id);
            $objWorkSheetCategory->setCellValue("B".$category_row,  $row->id);
            $category_row++;
        }
        if ($category_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST',
                    $objWorkSheetCategory,
                    'A2:A'.($category_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST_ALL',
                    $objWorkSheetCategory,
                    'A2:B'.($category_row-1),
                    false,
                    NULL
                )
            );
        }

        // Brand Sheet
        $menu_row = 2;
        foreach ( $brands_list as $row ) {
            $objWorkSheetBrand->setCellValue("A".$menu_row, $row->name.'###'.$row->id);
            $objWorkSheetBrand->setCellValue("B".$menu_row,  $row->id);
            $menu_row++;
        }
        if ($menu_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'BRAND_LIST',
                    $objWorkSheetBrand,
                    'A2:A'.($menu_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'BRAND_LIST_ALL',
                    $objWorkSheetBrand,
                    'A2:B'.($menu_row-1),
                    false,
                    NULL
                )
            );
        }

        // Write form data to a sheet (to be used when importing)
        $spreadsheet->setActiveSheetIndex(3);
        $spreadsheet->getActiveSheet()
            ->setCellValue(
                "A1",
                "company_id"
            );
        $spreadsheet->getActiveSheet()
            ->setCellValue(
                "B1",
                $company_id
            );
        $spreadsheet->getActiveSheet()
            ->setCellValue(
                "A2",
                "store_id"
            );
        $spreadsheet->getActiveSheet()
            ->setCellValue(
                "B2",
                $store_id
            );

        $objWorkSheetCategory->getProtection()->setSheet(true);
        $objWorkSheetBrand->getProtection()->setSheet(true);
        $objWorkSheetForm->getProtection()->setSheet(true);
        $objWorkSheetAttrVal->getProtection()->setSheet(true);

        // Attribute List
        $attr_col = 0;
        $attr_val_label_col = 1;
        $attr_val_key_col = 2;
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            // $attr_slug = 'attr_'. strtoupper(str_replace(" ", "_", $attribute_name)."_K".$attribute_id);
            $attr_slug = 'attr_'. url_title($attribute_name, '_', TRUE).'_k'.$attribute_id;

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_col);
            $objWorkSheetProduct->setCellValue($attr_col_label.'1', 'Attribute '. $attribute_name);

            $attr_val_label_letter = Coordinate::stringFromColumnIndex($attr_val_label_col);
            $attr_val_key_letter   = Coordinate::stringFromColumnIndex($attr_val_key_col);

            $objWorkSheetAttrVal->setCellValue($attr_val_label_letter."1", $attribute_name);
            $objWorkSheetAttrVal->setCellValue($attr_val_key_letter."1", $attr_slug);

            $a_key = 0;
            foreach ($attribute_values[$attribute_id] as $attr_val_id => $attr_val_name) {
                $objWorkSheetAttrVal->setCellValue($attr_val_label_letter.($a_key+2), $attr_val_name);
                $objWorkSheetAttrVal->setCellValue($attr_val_key_letter.($a_key+2), 'attr_val_'.$attr_val_id);
                $a_key++;
            }

            $objWorkSheetAttrVal->getStyle($attr_val_label_letter.'1:'.$attr_val_key_letter.'1')->getFont()->setBold(true);

            if ($a_key >= 0) {
                $spreadsheet->addNamedRange(
                    new NamedRange(
                        $attr_slug,
                        $objWorkSheetAttrVal,
                        $attr_val_label_letter . '2:'.$attr_val_label_letter.($a_key+1),
                        false,
                        NULL
                    )
                );

            }

            $attr_val_label_col += 2;
            $attr_val_key_col += 2;

            $attr_col++;
        }


        $spreadsheet->setActiveSheetIndex(0);

        //Product type
        $spreadsheet->getActiveSheet()->setDataValidation(
            'D2:D1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"simple,variable"')
        );

        //Prodcut commission
        $spreadsheet->getActiveSheet()->setDataValidation(
            'E2:E1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Commission Error')
                ->setOperator(DataValidation::OPERATOR_GREATERTHAN)
                ->setError('invalid commission value.')
                ->setPrompt('Only numbers between 0 and 100 are allowed.')
                ->setFormula1(-1)
                ->setFormula2(100)
        );

        $spreadsheet->getActiveSheet()->setDataValidation(
            'F2:F1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Category error')
                ->setError('Category name is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('CATEGORY_LIST')
        );

        $spreadsheet->getActiveSheet()->setDataValidation(
            'G2:G1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Brand error')
                ->setError('Brand name is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('BRAND_LIST')
        );

        $spreadsheet->getActiveSheet()->setDataValidation(
            'H2:H1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Regular Price Error')
                ->setOperator(DataValidation::OPERATOR_GREATERTHAN)
                ->setError('Value must be greater than zero.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
        );

        $spreadsheet->getActiveSheet()->setDataValidation(
            'I2:I1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Sale Price Error')
                ->setError('Value must be greater than zero and less than or equal to Regular Price.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
                ->setFormula2('H2')
        );

        //Stock
        $spreadsheet->getActiveSheet()->setDataValidation(
            'J2:J1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(J2)')
        );

        //Weight
        $spreadsheet->getActiveSheet()->setDataValidation(
            'M2:M1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(M2)')
        );

        //Length
        $spreadsheet->getActiveSheet()->setDataValidation(
            'N2:N1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(N2)')
        );

        //width
        $spreadsheet->getActiveSheet()->setDataValidation(
            'O2:O1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(O2)')
        );

        //Height
        $spreadsheet->getActiveSheet()->setDataValidation(
            'P2:P1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(P2)')
        );


        //Buy 1 Get 1
        $spreadsheet->getActiveSheet()->setDataValidation(
            'Q2:Q1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );

        //Buy 2 GET 2
        $spreadsheet->getActiveSheet()->setDataValidation(
            'R2:R1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );

        //Free Shipping
        $spreadsheet->getActiveSheet()->setDataValidation(
            'S2:S1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );
        //Allow Review
        $spreadsheet->getActiveSheet()->setDataValidation(
            'T2:T1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );
        //Require Review Moderation
        $spreadsheet->getActiveSheet()->setDataValidation(
            'U2:U1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );
        //Enable Cart COmment
        $spreadsheet->getActiveSheet()->setDataValidation(
            'V2:V1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );
        // Image Action
        $spreadsheet->getActiveSheet()->setDataValidation(
            'Y2:Y1048576',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Image action value error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"KEEP EXISTING,REPLACE"')
        );

        // Attributes
        $attr_j = 0;
        $attribute_col_labels = [];
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_j);
            // $attr_slug      = 'attr_'. strtoupper(str_replace(" ", "_", $attribute_name)."_K".$attribute_id);
            $attr_slug      = 'attr_'. url_title($attribute_name, '_', TRUE).'_k'.$attribute_id;

            $attribute_col_labels[$attribute_id] = $attr_col_label;

            $spreadsheet->getActiveSheet()->setDataValidation(
                "{$attr_col_label}2:{$attr_col_label}1048576",
                (new DataValidation())
                    ->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP )
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Attribute '. $attribute_name .': value error')
                    ->setError('Attribute '. $attribute_name .': value is not in list.')
                    ->setPromptTitle('Pick from list')
                    ->setFormula1($attr_slug)
            );

            $attr_j++;
        }

        // Product Status
        $extra_col_num = $attr_col_start+$attr_j;

        $status_col_label = Coordinate::stringFromColumnIndex($extra_col_num);

        $objWorkSheetProduct->setCellValue($status_col_label.'1', 'Status');
        $spreadsheet->getActiveSheet()->setDataValidation(
            "{$status_col_label}2:{$status_col_label}1048576",
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Product status error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"ACTIVE,INACTIVE"')
        );
        $extra_col_num = $attr_col_start+$attr_j+1;

        $status_col_label = Coordinate::stringFromColumnIndex($extra_col_num);

        $objWorkSheetProduct->setCellValue($status_col_label.'1', 'Featured');
        $spreadsheet->getActiveSheet()->setDataValidation(
            "{$status_col_label}2:{$status_col_label}1048576",
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Product status error')
                ->setError('Value is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')
        );
        // $lcol_index = $extra_col_num ;
        // if ( count($this->data['_languages']) > 1 ){
        //  foreach ($this->data['_languages'] as $t_code => $t_name){
        //      if ( $t_code != 'en' ){
        //              $lcol_index++;
        //              $l_col_label = Coordinate::stringFromColumnIndex($lcol_index);
        //              $objWorkSheetProduct->setCellValue($l_col_label.'1', 'Name ('.$t_name.')');

        //              $lcol_index++;
        //              $l_col_label = Coordinate::stringFromColumnIndex($lcol_index);
        //              $objWorkSheetProduct->setCellValue($l_col_label.'1', 'Description ('.$t_name.')');
        //      }
        //  }
        // }



        if ( $is_blank == 0 ) {
            $product_data = [];

            $search_params = [
                'lang' => 'en',
                'include_archive' => 1,
                'company_id' => $company_id,
                'store_id' => $store_id,
                'category_ids' => $category_ids,
            ];
            $this->ecom_product_model->where('product.company_id', $company_id);


            if ( $store_id > 0 ) {
                $this->ecom_product_model->where('product.is_locked', 0);
            }
            $products = $this->ecom_product_model->products_all($search_params)->result_array();


            $product_ids = array_column($products, 'id');

            $product_category_ids = $this->ecom_product_model->get_product_categories($product_ids);
            if( !empty($products) ){
                $product_attribute_ids = array_column($products, 'product_variant_id');

                $product_attribute_list = $this->ecom_product_model->getProductAttributesFull($product_attribute_ids);

                $product_attributes = [];
                foreach ($product_attribute_list as $row) {
                    $product_attributes[$row['product_attribute_id']][] = $row;
                }

            }


            foreach ( $products as $product ) {
                $pid = $product['id'];
                if(!isset($product_data[$pid])){
                    $product_data[$pid] = $product;
                }

                $product_data[$pid]['categories'] = $product_category_ids[$product['id']] ?? [];

                if (! isset($product_data[$pid]['variant_list'][$product['product_variant_id']]) ) {

                    $t_attributes = [];

                    if ( $product['product_type'] == self::PRODUCT_TYPE_VARIABLE ) {
                        $t_attributes = $product_attributes[$product['product_variant_id']];
                    }

                    $product_data[$pid]['variant_list'][$product['product_variant_id']] = [
                        'stock_quantity' => $product['stock_quantity'],
                        'regular_price' => $product['regular_price'],
                        'sale_price' => $product['sale_price'],
                        'image' => $product['image'],
                        'image_action' => $product['image_action'],
                        'barcode' => $product['barcode'],
                        'width' => $product['width'],
                        'height' => $product['height'],
                        'length' => $product['length'],
                        'weight' => $product['weight'],
                        'sku' => $product['sku'],
                        'attributes' => $t_attributes,
                    ];

                }
            }



            $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
            $spreadsheet->getDefaultStyle()->getProtection()->setLocked(false);

            $row = 2;

            foreach ( $product_data as $product_id => $product ) {



                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "A{$row}",
                        $product_id
                    );

                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "B{$row}",
                        $product['product_name']
                    );

                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "C{$row}",
                        $product['description']
                    );

                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "D{$row}",
                        ($product['product_type']=="2")?'variable':'simple'
                    );

                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "E{$row}",
                        $product['product_commission']
                    );


                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "Q{$row}",
                        ($product['buy_1_get_1']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "R{$row}",
                        ($product['buy_2_get_1']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "S{$row}",
                        ($product['free_shipping']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "T{$row}",
                        ($product['allow_review']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "U{$row}",
                        ($product['require_review_moderation']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "V{$row}",
                        ($product['enable_comment_box']==1)?'YES':'NO'
                    );
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "W{$row}",
                        $product['youtube_link']
                    );
                $t_brand = '';
                if ( !empty($product['brand_id']) && isset($brands[$product['brand_id']]) ) {
                    $t_brand = $brands[$product['brand_id']]."###".$product['brand_id'];
                }
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "G{$row}",
                        $t_brand
                    );


                // Categories
                $c_row = $row;
                foreach ($product['categories'] as $cat_id) {

                    if (! isset($categories[$cat_id]) ) {
                        continue;
                    }

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "F{$c_row}",
                            "{$categories[$cat_id]->name}###{$cat_id}"
                        );
                    $c_row++;
                }
                $spreadsheet->getActiveSheet()
                    ->setCellValue(
                        "{$status_col_label}{$row}",
                        ($product['active'] == 1 ? 'ACTIVE' : 'INACTIVE')
                    );

                // Variants
                $v_row = $row;
                foreach ($product['variant_list'] as $product_attribute_id => $variant) {
                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "H{$v_row}",
                            $variant['regular_price']
                        );

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "I{$v_row}",
                            $variant['sale_price']
                        );



                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "J{$v_row}",
                            $variant['stock_quantity']
                        );

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "K{$v_row}",
                            $variant['sku']
                        );

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "L{$v_row}",
                            $variant['barcode']??''
                        );

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "M{$v_row}",
                            $variant['weight']
                        );

                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "N{$v_row}",
                            $variant['length']
                        );
                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "O{$v_row}",
                            $variant['width']
                        );
                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "P{$v_row}",
                            $variant['height']
                        );



                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "X{$v_row}",
                            $variant['image']
                        );
                    $spreadsheet->getActiveSheet()
                        ->setCellValue(
                            "Y{$v_row}",
                            (!empty($variant['image_action']) ? $variant['image_action'] : "KEEP EXISTING")
                        );


                    if (! empty($variant['attributes']) ) {
                        foreach ($variant['attributes'] as $v_attribute) {
                            $t_col_label = $attribute_col_labels[$v_attribute['attribute_id']] ?? '';
                            if ( empty($t_col_label) ) continue;

                            $spreadsheet->getActiveSheet()
                                ->setCellValue(
                                    "{$t_col_label}{$v_row}",
                                    $v_attribute['attribute_values']
                                );
                        }
                    }

                    $v_row++;
                }

                // Menus
                $m_row = $v_row;
                // foreach ($product['menus'] as $menu_id) {
                //  if (! isset($menus[$menu_id]) ) {
                //      continue;
                //  }

                //     $spreadsheet->getActiveSheet()
                //         ->setCellValue(
                //             "G{$m_row}",
                //             "{$menus[$menu_id]->name}###{$menu_id}"
                //         );
                //     $m_row++;
                // }

                $row = max([$c_row,$v_row, $m_row]);
            }


        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setVisible(false);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(TRUE);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(30);

        $spreadsheet->getActiveSheet()->getColumnDimension($status_col_label)->setWidth(20);

        for ($j = $attr_col_start; $j < $attr_col_start+$attr_col; $j++) {
            $attr_col_label = Coordinate::stringFromColumnIndex($j);
            $spreadsheet->getActiveSheet()->getColumnDimension($attr_col_label)->setWidth(20);
        }



        $spreadsheet->getActiveSheet()->getStyle("A1:Y1")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->freezePane('C2');

        $spreadsheet->getProperties()->setCreator('myevents')
            ->setLastModifiedBy('myevents')
            ->setTitle('Product Export')
            ->setSubject('Products Export List')
            ->setDescription('Product Export List')
            ->setKeywords('product list')
            ->setCategory('product list');

        ob_clean();
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header('Content-Disposition: attachment;filename="ProductsList-'.date('Y-m-d').'.xlsx"');

        header("Expires: 0"); // Date in the past
        // header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Cache-Control: private",false);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->setOffice2003Compatibility(true);
        $writer->save('php://output');
        exit;
    }
    public function export_submit_test(){
        set_time_limit(0);
        ini_set('memory_limit','-1');
        $attr_col_start = self::ATTRIBUTE_COL_START;
        ob_start();

        $spreadsheet                = new Spreadsheet();
        $objWorkSheetProduct        = new Worksheet($spreadsheet, 'Products');

        $objWorkSheetProduct->setCellValue('A1', 'ID');
        $objWorkSheetProduct->setCellValue('B1', 'Name *');

        $spreadsheet->addSheet($objWorkSheetProduct, 0);

        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->getActiveSheet()->getStyle("A1:Y1")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->freezePane('C2');

        $spreadsheet->getProperties()->setCreator('my-event')
            ->setLastModifiedBy('my-event')
            ->setTitle('Product Export')
            ->setSubject('Products Export List')
            ->setDescription('Product Export List')
            ->setKeywords('product list')
            ->setCategory('product list');

        ob_clean();
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header('Content-Disposition: attachment;filename="ProductsList-'.date('Y-m-d').'.xlsx"');

        header("Expires: 0"); // Date in the past
        // header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Cache-Control: private",false);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->setOffice2003Compatibility(true);
        $writer->save('php://output');
        exit;
    }

    function export(){
        $list = ProductModel::exportDetails( auth()->user()->id );

        $rows = array();
        $i = 1;
        foreach ($list as $key => $val) {
            $rows[$key]['i'] = $i;
            $rows[$key]['product_name'] = $val->product_name;
            $rows[$key]['category_name'] = ($val->category_name)??'-';
            $rows[$key]['vendor_name'] = ($val->vendor_name)??'-';
            $rows[$key]['stock'] = ($val->stock_quantity)??'0';
            $rows[$key]['sale_price'] = $val->sale_price;
            $rows[$key]['regular_price'] = $val->regular_price;
            $rows[$key]['created_date'] = date('d-m-Y h:i A',strtotime($val->product_created_date));
            $i++;
        }
        $headings = [
            "#",
            "Product Name",
            "Category",
            "Seller",
            "Stock",
            "Sale Price",
            "Regular Price",
            "Created Date",
        ];
        $coll = new ExportReports([$rows], $headings);
        $ex = Excel::download($coll, 'products_' . date('d_m_Y_h_i_s') . '.xlsx');
        if (ob_get_length()) ob_end_clean();
        return $ex;
    }

    public function download_format()
    {
        $vendorid = Auth::user()->id;
        
        $attr_col_start=15;
        $category= Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->limit(10)->get();

        
        $store = VendorModel::select('users.id AS id', 'vendor_details.company_name as name')
         ->join('vendor_details','vendor_details.user_id','=','users.id')
         ->where('users.role', '3')
         ->where('users.id', $vendorid)
         ->limit(10)
         ->get();
        $attribute = ProductModel:: getProductAttributes();
        $attribute_values = [];
        foreach ($attribute as $attr_val) {
            $attribute_values[$attr_val->attribute_id][$attr_val->attribute_values_id] = $attr_val->attribute_values;
        }

        foreach ($attribute_values as $attribute_id => $attr_val) {
            ksort($attr_val);
            $attribute_values[$attribute_id] = $attr_val;
        }
        $spreadsheet    = new Spreadsheet();
        $sheet1        = new Worksheet($spreadsheet, 'Products');
        $sheet2        = new Worksheet($spreadsheet, 'Categories');
        $sheet3        = new Worksheet($spreadsheet, 'Store');
        $sheet4        = new Worksheet($spreadsheet, 'Attributes');


        $sheet1->setCellValue('A1', 'Product Type');
        $sheet1->setCellValue('B1', 'Category');
        $sheet1->setCellValue('C1', 'Default Category');
        $sheet1->setCellValue('D1', 'Product Name');
        $sheet1->setCellValue('E1', 'Store Name');
        $sheet1->setCellValue('F1', 'SKU');
        $sheet1->setCellValue('G1', 'regular Price');
        $sheet1->setCellValue('H1', 'Sale Price');
        $sheet1->setCellValue('I1', 'Stock Quantity');
        $sheet1->setCellValue('J1', 'VAT');
        $sheet1->setCellValue('K1', 'Barcode');
        $sheet1->setCellValue('L1', 'Product Code');
        $sheet1->setCellValue('M1', 'Description');
        $sheet1->setCellValue('N1', 'Images(seperated by comma)');


        $sheet2->setCellValue("A1", "Category");
        $sheet2->setCellValue("B1", "ID");

        $sheet3->setCellValue("A1", "Store");
        $sheet3->setCellValue("B1", "ID");


        $spreadsheet->addSheet($sheet1, 0);
        $spreadsheet->addSheet($sheet2, 1);
        $spreadsheet->addSheet($sheet3, 2);
        $spreadsheet->addSheet($sheet4, 3);


        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet3->getStyle('A1:B1')->getFont()->setBold(true);

//category
        $brand_row = 2;
        $v=$b=$c=$p=[];
        foreach ($category as $row) {
            $sheet2->setCellValue("A".$brand_row, $row->name);
            $sheet2->setCellValue("B".$brand_row,  $row->id);
            $row->name=str_replace("'"," ",$row->name);
            $p[]= $row->id.'#'.$row->name;
            $brand_row++;
        }
        if ($brand_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST',
                    $sheet2,
                    'A1:A'.($brand_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST_ALL',
                    $sheet2,
                    'A1:B'.($brand_row-1),
                    false,
                    NULL
                )
            );
        }

//STORE
        $store_row = 2;
        foreach ($store as $row) {
            $sheet3->setCellValue("A".$store_row, $row->name);
            $sheet3->setCellValue("B".$store_row,  $row->id);
            $store_row++;
            $row->name=str_replace("'"," ",$row->name);
            $v[]= $row->id.'#'.$row->name;
        }
        if ($store_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'STORE_LIST',
                    $sheet3,
                    'A1:A'.($store_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'STORE_LIST_ALL',
                    $sheet3,
                    'A1:B'.($store_row-1),
                    false,
                    NULL
                )
            );
        }



        $sheet2->getProtection()->setSheet(true);
        $sheet3->getProtection()->setSheet(true);



        // Attribute List
        $attr_col = 0;
        $attr_val_label_col = 1;
        $attr_val_key_col = 2;

        $attributes=[];
        foreach ($attribute as $row) {
            $attributes[$row->attribute_id]=$row->attribute_name;
        }
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            $attr_slug = 'attr_'. url_title($attribute_name, '_', TRUE).'_k'.$attribute_id;

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_col);
            $sheet1->setCellValue($attr_col_label.'1', 'Attribute '. $attribute_name);

            $attr_val_label_letter = Coordinate::stringFromColumnIndex($attr_val_label_col);
            $attr_val_key_letter   = Coordinate::stringFromColumnIndex($attr_val_key_col);

            $sheet4->setCellValue($attr_val_label_letter."1", $attribute_name);
            $sheet4->setCellValue($attr_val_key_letter."1", $attr_slug);

            $a_key = 0;
            foreach ($attribute_values[$attribute_id] as $attr_val_id => $attr_val_name) {
                $sheet4->setCellValue($attr_val_label_letter.($a_key+2), $attr_val_name);
                $sheet4->setCellValue($attr_val_key_letter.($a_key+2), 'attr_val_'.$attr_val_id);
                $a_key++;
            }

            $sheet4->getStyle($attr_val_label_letter.'1:'.$attr_val_key_letter.'1')->getFont()->setBold(true);

            if ($a_key >= 0) {
                $spreadsheet->addNamedRange(
                    new NamedRange(
                        $attr_slug,
                        $sheet4,
                        $attr_val_label_letter . '2:'.$attr_val_label_letter.($a_key+1),
                        false,
                        NULL
                    )
                );

            }

            $attr_val_label_col += 2;
            $attr_val_key_col += 2;

            $attr_col++;
        }



        $sheet1->getStyle('A1:'.$sheet1->getHighestDataColumn().'1')->getFont()->setBold(true);
        $spreadsheet->setActiveSheetIndex(0);


        // PRODUCT TYPE
        $sheet1->setDataValidation(
            'A2:A1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Product error')
                ->setError('Product type is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"Simple,Variable"'));
        // CATEGORY
        $sheet1->setDataValidation(
            'B2:B1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Category error')
                ->setError('Category is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1(sprintf('"%s"',implode(',',$p)))

        );
        // DEFAULT CATEGORY
        $sheet1->setDataValidation(
            'C2:C1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Default category error')
                ->setError('Default Category is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1(sprintf('"%s"',implode(',',$p)))

        );
//brand
        $sheet1->setDataValidation(
            'E2:E1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Vendor error')
                ->setError('Vendor is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1(sprintf('"%s"',implode(',',$v)))


        );

        // Regular Price, Sale Price & Stock
        $sheet1->setDataValidation(
            'G2:G1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Regular Price Error')
                ->setOperator(DataValidation::OPERATOR_GREATERTHAN)
                ->setError('Value must be greater than zero.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
        );

        $sheet1->setDataValidation(
            'H2:H1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Sale Price Error')
                ->setError('Value must be greater than zero and less than or equal to Regular Price.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
                ->setFormula2('G2')
        );

        // stock count
        $sheet1->setDataValidation(
            'I2:I1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(I2)')
        );
        // allow back orders
        $sheet1->setDataValidation(
            'J2:J1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Vendor error')
                ->setError('Vendor is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"YES,NO"')


        );

        // Attributes
        $attr_j = 0;
        $attribute_col_labels = [];
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_j);

            $attr = [];
            foreach ($attribute_values[$attribute_id] as $attr_val_id => $attr_val_name) {
                $attr[]      = $attr_val_id.'#'.$attr_val_name;
            }
            $attribute_col_labels[$attribute_id] = $attr_col_label;

            $spreadsheet->getActiveSheet()->setDataValidation(
                "{$attr_col_label}2:{$attr_col_label}1048576",
                (new DataValidation())
                    ->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP )
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Attribute '. $attribute_name .': value error')
                    ->setError('Attribute '. $attribute_name .': value is not in list.')
                    ->setPromptTitle('Pick from list')
                    ->setFormula1(sprintf('"%s"',implode(',',$attr)))
            );

            $attr_j++;
        }

        $sheet1->getColumnDimension('A')->setWidth(40);
        $sheet1->getColumnDimension('B')->setWidth(40);
        $sheet1->getColumnDimension('C')->setWidth(40);
        $sheet1->getColumnDimension('D')->setWidth(30);
        $sheet1->getColumnDimension('E')->setWidth(40);
        $sheet1->getColumnDimension('F')->setWidth(40);
        $sheet1->getColumnDimension('G')->setWidth(40);
        $sheet1->getColumnDimension('H')->setWidth(30);
        $sheet1->getColumnDimension('I')->setWidth(30);
        $sheet1->getColumnDimension('J')->setWidth(40);
        $sheet1->getColumnDimension('K')->setWidth(40);
        $sheet1->getColumnDimension('L')->setWidth(40);
        $sheet1->getColumnDimension('M')->setWidth(40);
        $sheet1->getColumnDimension('N')->setWidth(30);
        $sheet1->getColumnDimension('O')->setWidth(40);
        $sheet1->getColumnDimension('P')->setWidth(30);
        $sheet1->getColumnDimension('Q')->setWidth(30);
        $sheet1->getColumnDimension('R')->setWidth(30);
        $sheet1->getColumnDimension('S')->setWidth(30);
        $sheet1->getColumnDimension('T')->setWidth(30);


        $date = date('d-m-y-'.substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "product_list".$date.".xlsx";

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);
            $content = file_get_contents($filename);
        } catch(Exception $e) {
            exit($e->getMessage());
        }

        header("Content-Disposition: attachment; filename=".$filename);

        unlink($filename);
        exit($content);


    }
    function import(Request $request)
    {

        $temp_product_id='';

        $validator = Validator::make($request->all(), [
            'select_file'  => 'required|mimes:xls,xlsx'
        ],
            [
                'select_file.required' => 'A file is required',
                'select_file.mimes' => 'should be in format (.xls,.xlsx)'
            ]);
        if ($validator->fails()) {

            $message = "Validation error occured";
            $errors = implode(",",$validator->messages()->all());
            return response()->json(['success' => 0, 'message' => $errors]);
        }



        // $path = $request->file('select_file')->getRealPath();
        $path1 = $request->file('select_file')->store('temp');
        $path=storage_path('app').'/'.$path1;
        $data = Excel::toArray(new ProductImport, $path);
        $temp_product_id='';
        if(isset($data[0][1]))
        {

            $attribute = ProductModel:: getProductAttributes();
            $attribute_values = [];
            foreach ($attribute as $attr_val) {
                $attribute_values[$attr_val->attribute_id][$attr_val->attribute_values_id] = $attr_val->attribute_values;
            }

            foreach ($attribute_values as $attribute_id => $attr_val) {
                ksort($attr_val);
                $attribute_values[$attribute_id] = $attr_val;
            }

            $attributes=[];
            $attributes = array_column($attribute->toArray(), 'attribute_id', 'attribute_name');
            asort($attributes);


            $attribute_cols = [];

            $attr_col_start = 20;
            $attr_col = 0;
            $status_col_label = '';

            foreach($data[0] as $key => $row)
            {
                $flag=0;

                if ($key == 0) {
                    foreach ($row as $col_name => $attr_name) {
                        $t_name = explode(' ', $attr_name);
                        if ($t_name[0] == 'Attribute') {
                            $p_name = trim(str_replace($t_name[0], '', $attr_name));
                            $attribute_cols[$col_name] = $p_name;
                            $attr_col++;
                        }
                    }
                    $extra_col_num = $attr_col_start+$attr_col;
                    $status_col_label = Coordinate::stringFromColumnIndex($extra_col_num);
                    continue;
                }



                if($row[0]!='' && $row[1]!='' && $row[6]!='' && $row[7]!='' && $row[8]!=''){
                    $temp_product_id='';
                    $flag=0;
                }

                // INSERT MULTIPLE CATEGORY
                if($row[0]=='' && $row[1]!='' && $temp_product_id!=''){
                    $category_id=explode('#',$row[1]);
                    $category_data = array('product_id'=>$temp_product_id,'category_id'=>$category_id[0]);
                    DB::table('product_category')->insert($category_data);
                    $flag=1;
                }

                if ($row[0]=='' && $row[6]!='' && $row[7]!='' && $row[8]!='') {
                    $product_id=$temp_product_id;
                    $row_variants = [];
                    foreach ($attribute_cols as $t_col_name => $t_attr_name) {
                        $t_attr_val = $this->filterCell($row[$t_col_name]);

                        if (! empty($t_attr_val) ) {
                            $a=explode('#',$t_attr_val);
                            $t_attr_val=$a[1];
                            $t_attribute_id = $attributes[$t_attr_name];
                            $t_attribute_val_id =$a[0];

                            $row_variants[] = [
                                'attribute_id' => $t_attribute_id,
                                'attribute_values_id' => (int)$t_attribute_val_id,
                                'product_id'=>$product_id
                            ];


                        }
                    }

                    if ( !empty($row_variants)) {


                        $product_multiple_variant = [
                            'regular_price' => $row[6],
                            'sale_price' =>$row[7],
                            'product_id' =>$product_id,
                            'stock_quantity' =>$row[8],
                            'allow_back_order'=>($row[9]=="YES")?1:0,
                            'product_desc' => $row[12],
                            'product_full_descr' =>$row[13],
                            'barcode'  =>$row[10],
                            'pr_code'  =>$row[11],

                        ];
                        $pAttr = ProductAttribute::create($product_multiple_variant);
                        $product_attribute_id = $pAttr->product_attribute_id;
                        $product_variations=[];
                        foreach ($row_variants as $key=> $variant) {
                            $product_variations[] = [
                                'attribute_id' => $variant['attribute_id'],
                                'attribute_values_id' => $variant['attribute_values_id'],
                                'product_id'=>$variant['product_id'],
                                'product_attribute_id' => $product_attribute_id,
                            ];
                        }
                        if(!empty($product_variations)){
                            DB::table('product_variations')-> insert($product_variations);
                            DB::table('product_selected_attributes')-> insert($row_variants);
                        }
                    }
                    if($row[19]){
                        $img=[];
                        $image=explode(',',$row[19]);
                        for($i=0;$i<count($image);$i++){
                            $img[$i]['image']=$image[$i];
                            $img[$i]['product_id']=$product_id;
                            $img[$i]['product_attribute_id']=$product_attribute_id;
                        }
                        DB::table('product_temp_image')->insert($img);
                    }
                    $flag=1;
                }


                if($flag){
                    continue;
                }
                $a=explode('#',$row[2]);
                $default_category_id=isset($a[0])?$a[0]:0;

                $a=explode('#',$row[4]);
                $product_vender_id=isset($a[0])?$a[0]:0;

                $insert_data =  [
                    'product_type' => ($row[0]=="Variable")?2:1,
                    'product_name' => $row[3],
                    'product_name_arabic' =>  $row[3],
                    'product_desc_full' => $row[13],
                    'product_desc_full_arabic' =>  $row[13],
                    'product_desc_short' => $row[12],
                    'product_desc_short_arabic' =>  $row[12],
                    'product_unique_iden'=>$row[5],
                    'product_created_by'=>(int)$product_vender_id,
                    'product_created_date'=> date('Y-m-d H:i:s'),
                    'product_vender_id' =>(int) $product_vender_id,
                    'meta_title' => $row[14],
                    'meta_keyword' => $row[15],
                    'meta_description' => $row[16],
                    'default_category_id' => (int)$default_category_id,
                    'product_status' => 1,
                    'product_vendor_status' => 0,
                    'product_deleted' => 0,
                    'product_variation_type' => 1,
                    'product_taxable' => 1,

                ];

                if(!empty($insert_data))
                {
                    $temp_product_id=$product_id= ProductModel::insertGetId($insert_data);
                    $spec_title=explode('#',$row[17]);
                    $spec_description=explode('#',$row[18]);

                    if(!empty($spec_title)){
                        for($i=0;$i<count($spec_title);$i++) {

                            DB::table('product_specifications')->insert(['spec_title'=>$spec_title[$i],'spec_descp'=>isset($spec_description[$i])?$spec_description[$i]:'','product_id'=>$product_id]);
                        }

                    }


                    $category_id=explode('#',$row[1]);
                    $category_data = array('product_id'=>$product_id,'category_id'=>(int)$category_id[0]);
                    DB::table('product_category')->insert($category_data);


// image



                    // varients
                    if ($row[0]=='Variable' && $row[6]!='' && $row[7]!='' && $row[8]!='') {
                        $product_id=$temp_product_id;
                        $row_variants = [];
                        foreach ($attribute_cols as $t_col_name => $t_attr_name) {
                            $t_attr_val = $this->filterCell($row[$t_col_name]);

                            if (! empty($t_attr_val) ) {
                                $a=explode('#',$t_attr_val);
                                $t_attr_val=$a[1];
                                $t_attribute_id = $attributes[$t_attr_name];
                                $t_attribute_val_id =$a[0];

                                $row_variants[] = [
                                    'attribute_id' => $t_attribute_id,
                                    'attribute_values_id' => (int)$t_attribute_val_id,
                                    'product_id'=>$product_id
                                ];


                            }
                        }

                        if ( !empty($row_variants)) {

                            $product_multiple_variant = [
                                'regular_price' => $row[6],
                                'sale_price' =>$row[7],
                                'product_id' =>$product_id,
                                'stock_quantity' =>$row[8],
                                'allow_back_order'=>($row[9]=="YES")?1:0,
                                'product_desc' => $row[12],
                                'product_full_descr' =>$row[13],
                                'barcode'  =>$row[10],
                                'pr_code'  =>$row[11],
                            ];
                            $pAttr = ProductAttribute::create($product_multiple_variant);
                            $product_attribute_id = $pAttr->product_attribute_id;
                            $product_variations=[];
                            foreach ($row_variants as $key=> $variant) {
                                $product_variations[] = [
                                    'attribute_id' => $variant['attribute_id'],
                                    'attribute_values_id' => $variant['attribute_values_id'],
                                    'product_id'=>$variant['product_id'],
                                    'product_attribute_id' => $product_attribute_id,
                                ];
                            }
                            if(!empty($product_variations)){
                                DB::table('product_variations')-> insert($product_variations);
                                DB::table('product_selected_attributes')-> insert($row_variants);
                            }
                        }

                    }
                    else {
                        $product_simple_variant = [
                            'regular_price' => $row[6],
                            'sale_price' =>$row[7],
                            'product_id' =>$product_id,
                            'stock_quantity' =>$row[8],
                            'allow_back_order'=>($row[9]=="YES")?1:0,
                            'product_desc' => $row[12],
                            'product_full_descr' =>$row[13],
                            'barcode'  =>$row[10],
                            'pr_code'  =>$row[11],
                            // 'image' => implode(",",$imagesList)12
                        ];
                        $pAttr = ProductAttribute::create($product_simple_variant);
                        $product_attribute_id = $pAttr->product_attribute_id;
                        ProductModel::updateDefaultAttribute($product_id,$product_attribute_id);
                    }

                    if($row[19]){
                        $img=[];
                        $image=explode(',',$row[19]);
                        for($i=0;$i<count($image);$i++){
                            $img[$i]['image']=$image[$i];
                            $img[$i]['product_id']=$product_id;
                            $img[$i]['product_attribute_id']=$product_attribute_id;
                        }
                        DB::table('product_temp_image')->insert($img);
                    }
                }

                //end
            }

            return response()->json(['success' => 1, 'message' => 'Excel Data Imported successfully.']);
        }
        return response()->json(['success' => 0, 'message' => 'Import failed.']);

    }


    public function unzip_image(Request $request){
        $validator = Validator::make($request->all(), [
            'zip_file'  => 'required|mimes:zip'
        ],
            [
                'zip_file.required' => 'A file is required',
                'zip_file.mimes' => 'should be in format (.zip)'
            ]);
        if ($validator->fails()) {

            $message = "Validation error occured";
            $errors = implode(",",$validator->messages()->all());
            return response()->json(['success' => 0, 'message' => $errors]);
        }
        $file_name = $_FILES['zip_file']['name'];
        $array = explode(".", $file_name);
        $name = $array[0];
        $ext = $array[1];
        if($ext == 'zip')
        {
            $path =  './'.config("global.upload_path").'products_zip/';
            $new_path =  './'.config("global.upload_path").'products/';
            $location = $path . $file_name;
            move_uploaded_file($_FILES['zip_file']['tmp_name'], $location);

            $zip = new ZipArchive;
            if($zip->open($location))
            {
                $zip->extractTo($path);
                $zip->close();
            }

            $all_files = glob("{$path}/*.*");
            $image_names = [];
            for ($i = 0; $i < count($all_files); $i++) {
                $image_name = $all_files[$i];
                $supported_format = array('jpg','jpeg','png');
                $basename = pathinfo($image_name, PATHINFO_BASENAME);
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                if ( in_array($ext, $supported_format) ) {
                    $image_names[] =$basename;// [$image_name, $ext];
                }
            }


            if(!empty($image_names)){
                $products=DB::table('product_temp_image')->whereIn('image',$image_names)->get();
                if($products->isEmpty()){
                    return response()->json(['success' => 0, 'message' => 'No Products found']);
                }

                foreach($products as $row){
                    copy($path.$name.'/'.$row->image, $new_path . $row->image);
                    unlink($path.$name.'/'.$row->image);

                    $res=ProductAttribute::where(['product_id'=>$row->product_id,'product_attribute_id'=>$row->product_attribute_id])->first();
                    if($res){
                        $image=($res->image)?$res->image.','.$row->image:$row->image;
                        ProductAttribute::where(['product_id'=>$row->product_id,'product_attribute_id'=>$row->product_attribute_id])->update(['image'=>$image]);
                        $products=DB::table('product_temp_image')->where(['image'=>$row->image,'product_attribute_id'=>$row->product_attribute_id])->delete();
                    }
                }
                unlink($location);
                $this->deleteDirectory($path . $name);
                return response()->json(['success' => 1, 'message' => 'Images uploaded successfully.']);
            }else{
                return response()->json(['success' => 0, 'message' => 'No Images found']);
            }


        }else{
            return response()->json(['success' => 0, 'message' => 'Invalid Extension,only .zip is allowed.']);
        }


    }

    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
    public function filterCell( $val, $default='' )
    {
        if (($val == "#N/A") || (substr($val, 0, 3) == "Err") || ($val == "#VALUE!") || ($val == "#REF!") ) {
            return $default;
        }

        return $val;
    }

}
