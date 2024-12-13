<?php 
namespace App\Traits;
 
use Illuminate\Http\Request;
 
trait StoreImageTrait {
 
    /**
     * Does very basic image validity checking and stores it. Redirects back if somethings wrong.
     * @Notice: This is not an alternative to the model validation for this field.
     *
     * @param Request $request
     * @return $this|false|string
     */
    public function verifyAndStoreImage( Request $request, $fieldname = 'image', $directory = 'unknown' ,$croped_file = '') 
    {
        if($file = $request->file($fieldname)){

            if(isset($request->cropped_upload_image) && $request->cropped_upload_image){
                $image_parts = explode(";base64,", $request->cropped_upload_image);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $imageName = uniqid() .time(). '.'.$image_type;
                 file_put_contents('./'.config("global.upload_path").'/'.$directory.'/'.$imageName, $image_base64);
                
                return $imageName;
            } else {
                $dir = config('global.upload_path')."/".$directory;
                $imageName = uniqid().time().".".$file->getClientOriginalExtension();
                $request->$fieldname->move($dir, $imageName);
                
                // $response = image_upload($request,'product',$fieldname);
                // if($response['status']){
                //     $imageName = $response['link'];
                // }
                // dd($imageName );
                return $imageName;

            }
        }

        if(isset($croped_file) && $croped_file){
            $image_parts = explode(";base64,", $croped_file);
            // dd($request->{$fieldname});
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $imageName = uniqid() .time(). '.'.$image_type;
            file_put_contents('./'.config("global.upload_path").'/'.$directory.'/'.$imageName, $image_base64);
            return $imageName;
        }
 
        return null;
 
    }
 
}