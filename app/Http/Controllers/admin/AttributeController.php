<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator,DB;
use App\Models\Attribute;
use App\Models\Attribute_values;
use App\Models\AttributeTypes;
use App\Models\Common;
class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Attribute::whereIn('attribute_status',[1,0])->where('is_deleted',0)->orderBy('attribute_id','desc')->paginate(5);
        $attribute_types = AttributeTypes::where('attribute_type_status',1)->get();
        $page_heading = "Attribute";
        $filter = [];
        $params = [];
        $params['search_key'] = $_GET['search_key'] ?? '';
        $search_key = $params['search_key'];
        return view("admin.attribute.list",compact('list','page_heading','search_key','attribute_types'));
    }

    public function save(Request $request)
    {
        
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $redirectUrl = '';
        $id      = $request->id;

        $rules   = [
            'attribute_name'      => 'required',
            
           
        ];

        $validator = Validator::make($request->all(),$rules,
        [
            'attribute_name.required' => 'Attribute Name required',
            'attribute_name_ar.required' => 'Arabic name required',
           
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }else{
            $check =  Attribute::where(['is_deleted'=>0,'attribute_name'=>$request->attribute_name])->where('attribute_id','!=',$request->attribute_id)->get();
            if($check->count() == 0)
            {


            $input = $request->all();
            $i_data = [ 
                'attribute_name'        => (string)$request->attribute_name,
                'attribute_name_arabic' => (string)$request->attribute_name_ar,
                'attribute_status'      => (int)$request->attribute_status,
                'attribute_type'        => $request->attribute_type,
                'attribute_id'          => (int)$request->attribute_id
            ];
            $status = Attribute::saveData($i_data);
            if($status) {
                $message  = "Attribute saved successfully";
            }
            }
            else
            {
                $message  = "Attribute already exist";
                $alert = array(
               'attribute_name'           => $message
                );
                $status = "0";
                $message = "Validation error occured";
                $errors = $alert;
            }
            
        }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
    }

    public function edit($id="")
    {
        $details = Attribute::find($id)->toArray();
        print_R(json_encode($details));
    }

    function change_status(Request $request){
        $status  = "0";
        $message = "";
        if(Attribute::where('attribute_id',$request->id)->update(['attribute_status'=>$request->status])){
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

    public function delete_attribute($id = '')
    {
        $check = DB::table('product_selected_attributes')->where(['attribute_id'=>$id])->get();
        if($check->count() > 0){
            $status = "0";
            $message = "Attribute used by some products";
            echo json_encode(['status' => $status, 'message' => $message]);
        }else{
        Attribute::where('attribute_id', $id)->update(['is_deleted' => 1]);
        $status = "1";
        $message = "Attribute removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
        }
    }

    public function attribute_values($id) 
    {
        $page_heading = "Attribute Values";
        $datamain    = Attribute::find($id)->toArray();
        
        if($datamain){
           
        }else{
            abort(404);
        }
        
        //$list = Attribute_values::where('is_deleted',0)->orderBy('attribute_values_id','desc')->paginate(5);

        $list = Attribute_values::get_data($id);
       
        return view("admin.attribute.att_values",compact('page_heading','list','datamain'));
    }
    public function save_atr_values(Request $request)
    {
         
        $status  = "0";
        $message = "";
        $o_data  = [];
        $errors  = [];
        $redirectUrl = '';
        $id      = $request->id;

        $rules   = [
            'txt_attr_name'                 => 'required',
            'txt_attr_value_in'             => 'required',
            'txt_attr_val_name'             => 'required',
            //'txt_attr_val_name_arabic'      => 'required',
            
           
        ];
        $validator = Validator::make($request->all(),$rules,
        [
            'txt_attr_name.required' => 'Attribute Name required',
            'txt_attr_value_in.required' => 'Attribute Value in required',
            'txt_attr_val_name.required' => 'Attribute Value name required',
            'txt_attr_val_name_arabic.required' => 'Attribute Value name Arabic required',
        ]);
        if ($validator->fails()) {
            $status = "0";
            $message = "Validation error occured";
            $errors = $validator->messages();
        }else{

            //check already exist
            if(empty($request->attribute_values_id))
            {
                $check = Common::check_already('product_attribute_values',["attribute_values"=> "$request->txt_attr_val_name","attribute_id"=> "$request->txt_attr_name",'is_deleted' => 0]);
            }
            else
            {
                $check = Common::check_already_edit('product_attribute_values',["attribute_values"=> "$request->txt_attr_val_name","attribute_id"=> "$request->txt_attr_name",'is_deleted' => 0],'attribute_values_id', $request->attribute_values_id);
            }
            if($check)
            {
                $alert = array(
               'txt_attr_val_name'           => 'Attribute value already exist!'
                );
                $status = "0";
                $message = "Validation error occured";
                $errors = $alert;
            }
            else
            {
                $input = $request->all();
                $i_data = [ 
                'attribute_values_id'         => (int)$request->attribute_values_id,
                'txt_attr_name'               => (int)$request->txt_attr_name,
                'txt_attr_value_in'           => (int)$request->txt_attr_value_in,
                'txt_attr_val_name'           => (string)$request->txt_attr_val_name,
                'txt_attr_val_name_arabic'    => (string)$request->txt_attr_val_name_arabic,
                'txt_attr_color'              => (string)$request->txt_attr_color
                 ];
                 $status = Attribute_values::attributeValues($i_data);
                 if($status) {
                $message  = "Attribute value saved successfully";
                }
            }
            
            
        }
        echo json_encode(['status'=>$status,'message'=>$message,'errors'=>$errors]);
    }
    public function edit_attribute_value($id="")
    {
        $details = Common::get_data_array('product_attribute_values',['attribute_values_id' => $id],['is_deleted' => 0]);
        print_R(json_encode($details));
    }
    public function delete_attribute_value($id = '')
    {
        $check = DB::table('product_selected_attributes')->where(['attribute_values_id'=>$id])->get();
        if($check->count() > 0){
            $status = "0";
            $message = "Attribute used by some products";
            echo json_encode(['status' => $status, 'message' => $message]);
        }else{
        Common::update_db('product_attribute_values',['attribute_values_id' => $id],['is_deleted' => 1]);
        $status = "1";
        $message = "Attribute value removed successfully";
        echo json_encode(['status' => $status, 'message' => $message]);
        }
    }
}