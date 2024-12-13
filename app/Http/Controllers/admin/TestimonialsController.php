<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class TestimonialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
        if (!GetUserPermissions('testimonials_view')) {
            abort(404);
        }
        
        $page_heading = "Testimonials";
        $testimonial = Testimonial::where(['deleted' => 0])->orderby('id','desc')->get();

        return view('admin.testimonials.list', compact('page_heading', 'testimonial'));
     }
     public function create($id='')
    {

        $page_heading = "Testimonials";
        $mode = "create";
        $testimonial = "";
        if($id > 0)
        {
          $testimonial = Testimonial::where(['id'=>$id])->first();
          if ($testimonial) {
              $page_heading = "Testimonial ";
              $mode = "edit";



          }else {
              abort(404);
          }
        }
        return view("admin.testimonials.create", compact('page_heading','testimonial', 'id'));
    }
     public function store(Request $request)
     {
         $status = "0";
         $message = "";
         $o_data = [];
         $errors = [];
         $redirectUrl = '';

         $validator = Validator::make($request->all(), [
             'name' => 'required',
             'rating'=>'required'
         ]);
         if ($validator->fails()) {
             $status = "0";
             $message = "Validation error occured";
             $errors = $validator->messages();
         } else {
             $input = $request->all();
             $check_exist = Testimonial::where(['deleted' => 0, 'name' => $request->name])->where('id', '!=', $request->id)->get()->toArray();
             if (empty($check_exist)) {
                 $ins = [
                     'name' => $request->name,
                     'designation' => $request->designation,
                     'comment' => $request->comment,
                     'updated_at' => gmdate('Y-m-d H:i:s'),
                     'active' => $request->active,
                     'rating'=>$request->rating


                 ];

                 if ($file = $request->file("image")) {
                    $dir = config('global.upload_path') . "/" . config('global.user_image_upload_dir');
                    $file_name = time() . $file->getClientOriginalName();
                    $file->move($dir, $file_name);
                    //$file->storeAs(config('global.banner_image_upload_dir'),$file_name,'s3');
                    $ins['user_image'] = $file_name;
                }



                 if ($request->id != "") {

                     Testimonial::where('id',$request->id)->update($ins);
                     $news_id = $request->id;
                     $status = "1";
                     $message = "Testimonial updated succesfully";
                 } else {

                     $ins['created_at'] = gmdate('Y-m-d H:i:s');
                     $news_id = Testimonial::insertGetId($ins);

                     $status = "1";
                     $message = "Testimonial Added Successfully";
                 }

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
         $status = "0";
         $message = "";
         $o_data = [];
         $category = Testimonial::find($id);
         if ($category) {
             $category->deleted = 1;
             $category->active = 0;
             $category->updated_at = gmdate('Y-m-d H:i:s');

             $category->save();
             $status = "1";
             $message = "Testimonial removed successfully";
         } else {
             $message = "Sorry!.. You cant do this?";
         }

         echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

     }
     public function change_status(Request $request)
     {
         $status = "0";
         $message = "";
         if (Testimonial::where('id', $request->id)->update(['active' => $request->status])) {
             $status = "1";
             $msg = "Successfully activated";
             if (!$request->status) {
                 $msg = "Successfully deactivated";
             }
             $message = $msg;
         } else {
             $message = "Something went wrong";
         }
         echo json_encode(['status' => $status, 'message' => $message]);
     }
     public function deleteImage(Request $request)
     {
       if ($request->ajax()) {
           $status = 0;
           $message = '';
           $event_id = $request->news_id;
           $image = $request->image;
           $newsObj = News::find($event_id);
           $gallery = explode(",",$newsObj ->image);
           if (($key = array_search($image, $gallery)) !== false) {
              unset($gallery[$key]);
              $newsObj->image = implode(",",$gallery);
              $newsObj->save();
              $status = "1";
              $messsage = "Image Removed";
           }

           echo json_encode(['status' => $status, 'message' => $message]);

       }
     }

     public function comments(Request $request)
     {
       if (!check_permission('category','View')) {
           abort(404);
       }
       $page_heading = "News Comments";
       $news = \App\Models\News::with('comment','comment.commentedBy')->where(['id' => $request->id])->where(['deleted' => 0])->orderby('id','desc')->get()->first();

       return view('admin.news.comment', compact('page_heading', 'news'));
     }


    public function savecategory(Request $request)
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
            $check_exist = NewsCategories::where(['deleted' => 0, 'name' => $request->name, 'parent' => $request->parent_id])->where('id', '!=', $request->id)->get()->toArray();
            if (empty($check_exist)) {
                $ins = [
                    'name' => $request->name,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'updated_uid' => session("user_id"),
                    'parent' => $request->parent_id ?? 0,
                    'active' => $request->active,
                ];

                if($request->file("image")){

                    $response = image_save($request->file("image"),config('global.category_image_upload_dir'));
                    if($response['status']){
                        $ins['image'] = $response['link'];
                    }
                }
                if($request->file("banner_image")){
                    $response = image_save($request->file("banner_image"),config('global.category_image_upload_dir'));
                    if($response['status']){
                        $ins['banner_image'] = $response['link'];
                    }
                }
                if ($request->id != "") {
                    $category = NewsCategories::find($request->id);
                    $category->update($ins);
                    $status = "1";
                    $message = "News Category updated succesfully";
                } else {
                    $ins['created_uid'] = session("user_id");
                    $ins['created_at'] = gmdate('Y-m-d H:i:s');
                    NewsCategories::create($ins);
                    $status = "1";
                    $message = "News Category added successfully";
                }
            } else {
                $status = "0";
                $message = "Name should be unique";
                $errors['name'] = $request->name . " already added";
            }

        }
        echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    }
    public function categorydestroy($id)
    {
        $status = "0";
        $message = "";
        $o_data = [];
        $category = NewsCategories::find($id);
        if ($category) {
            $category->deleted = 1;
            $category->active = 0;
            $category->updated_at = gmdate('Y-m-d H:i:s');
            $category->updated_uid = session("user_id");
            $category->save();
            $status = "1";
            $message = "News Category removed successfully";
        } else {
            $message = "Sorry!.. You cant do this?";
        }

        echo json_encode(['status' => $status, 'message' => $message, 'o_data' => $o_data]);

    }
    public function category_change_status(Request $request)
    {
        $status = "0";
        $message = "";
        if (NewsCategories::where('id', $request->id)->update(['active' => $request->status])) {
            $status = "1";
            $msg = "Successfully activated";
            if (!$request->status) {
                $msg = "Successfully deactivated";
            }
            $message = $msg;
        } else {
            $message = "Something went wrong";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }

}
