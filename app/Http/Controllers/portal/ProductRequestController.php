<?php

namespace App\Http\Controllers\Portal;

use App\Models\ProductMasterRequest;
use App\Models\ProductModel;
use App\Models\AttributeAndValues;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ProductRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $page_heading = "Product Master Request";
        $categories = ProductMasterRequest::where(['deleted'=> 0,'vendor_id' => session("user_id")])->orderBy('id', 'DESC')->get();
        
        return view('portal.product_request.list', compact('page_heading', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_heading = "Product Master Request";
        $mode = "create";
        $id = "";
        $product_type = "";
        $name = "";
        $parent_id = "";
        $image = "";
        $active = "1";
        $banner_image = "";
        $description = "";
        $category = "";
        $specs = [];
        $categories = ProductMasterRequest::get();
        return view("portal.product_request.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','description','product_type','specs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $errors = [];
        $redirectUrl = '';

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        } else {
            $input = $request->all();
            $check_exist = ProductMasterRequest::where(['name' => $request->name,'deleted' => 0])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'vendor_id' => session("user_id"),
                    'description' => $request->description,
                    'product_type' => $request->product_type,
                    'category' => $request->category,
                ];

                if($request->file("banner_image")){
                    $response = image_upload($request,'category','banner_image');
                    if($response['status']){
                        $ins['banner_image'] = $response['link'];
                    }
                }
                if ($request->id != "") {
                    $inid = $request->id;
                    $category = ProductMasterRequest::find($request->id);
                    $category->update($ins);

                    AttributeAndValues::where('request_id',$request->id)->delete();
                    $status = "1";
                    $message = "Product Master Request updated succesfully";
                } else {
                    $inid = ProductMasterRequest::create($ins)->id;
                    $status = "1";
                    $message = "Product Master Request successfully sent";
                }
                //save attribute and value
                $attribute = $request->attribute;
                $valueget     = $request->value;
                foreach ($attribute as $key => $value) {

                    $attrval = [
                    'request_id'=> $inid,
                    'attribute' => $value,
                    'value' => $valueget[$key]
                    ];
                    AttributeAndValues::insert($attrval);
                    
                }
                
                
                //save attribute and value END

            } else {
                $status = "0";
                $message = "Name should be unique";
                $errors['name'] = $request->name . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $datamain = ProductMasterRequest::find($id);
        if ($datamain) {
            $page_heading = "Product Master Request";
            $mode = "edit";
            $id = $datamain->id;
            $name = $datamain->name;
            $parent_id = $datamain->parent_id;
            $image = $datamain->image;
            $active = $datamain->active;
            $banner_image = "";
            $product_type = $datamain->product_type;
            $description = $datamain->description;
            $category = $datamain->category;
            $specs = AttributeAndValues::where('request_id',$id)->get();
            $categories = ProductMasterRequest::where('id', '!=', $id)->get();
            return view("portal.product_request.create", compact('page_heading', 'category', 'mode', 'id', 'name', 'parent_id', 'image', 'active', 'categories', 'banner_image','product_type','description','specs'));
        } else {
            abort(404);
        }
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
    public function delete_re($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $category = ProductMasterRequest::find($id);
        if ($category) {
            
            
               $category->deleted = 1;
               $category->accepted = 0;
               $category->updated_at = gmdate('Y-m-d H:i:s');
               $category->save();
               $message = "Deleted successfully";
           
            
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (ProductMasterRequest::where('id', $request->id)->update(['accepted' => $request->status])) {
            $status = "1";
            $msg = "Successfully accepted";
            if (!$request->accepte) {
                $msg = "Successfully rejected";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $message = '';

            $items = $request->items;
            $items = explode(",", $items);
            $sorted = Categories::sort_item($items);
            if ($sorted) {
                $status = 1;
            }

            echo json_encode(['status' => $status, 'message' => $message]);

        } else {
            $page_heading = "Sort Categories";

            $list = Categories::where(['deleted' => 0, 'parent_id' => 0])->get();

            return view("admin.sort", compact('page_heading', 'list'));
        }
    }
}
