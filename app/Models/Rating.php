<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VendorDetailsModel;

class Rating extends Model
{
    use HasFactory;
    public function user() {
      return $this->hasOne(VendorModel::class,'id','user_id');
    }
    public function vendor() {
      return $this->hasOne(VendorModel::class,'id','vendor_id');
    }
    public static function avg_rating($where=[]){
        $ratingcount =  Rating::where($where)->get()->count();
        $ratingsum   =  Rating::where($where)->get()->sum('rating');  
        $avgrating   =  0;
        if($ratingcount != 0 && $ratingsum != 0)
        {
          $avgrating   =  $ratingsum/$ratingcount;  
        }
       return $avgrating;
    }
    public static function avg_rating_wherein($where=[]){
        
        $ratingcount =  Rating::wherein('service_id',$where)->get()->count();
        $ratingsum   =  Rating::wherein('service_id',$where)->get()->sum('rating');  
        $avgrating   =  0;
        if($ratingcount != 0 && $ratingsum != 0)
        {
          $avgrating   =  $ratingsum/$ratingcount;  
        }
       return $avgrating;
    }
    public static function rating_list($where=[]){
        
      $datamain = Rating::select('ratings.id','name','user_id','service_id','rating','title','comment','ratings.created_at','order_id','users.user_image')
            ->leftjoin('users','users.id','=','ratings.user_id')
            ->where($where)->orderBy('id','desc')->get();
           
            foreach ($datamain as $key => $value_rating) {
              $user = VendorDetailsModel::where('user_id',$value_rating->user_id)->first();
               
               if ($user && $user->logo) {
                $img = $user->logo;
               } else {
                $img = !$value_rating->user_image ? asset("storage/placeholder.png") : asset($value_rating->user_image);
               }
               $datamain[$key]->user_image = $img??'';
               $datamain[$key]->created_at = get_date_in_timezone($value_rating->created_at, 'Y-m-d H:i:s');
              
               $reply = RatingReply::where('rating_id',$value_rating->id)->get();
               foreach ($reply as $key_re => $value_re) {
                $reply[$key_re]->created_date = get_date_in_timezone($value_re->created_at, 'Y-m-d H:i:s');
                $vendor_details = VendorModel::find($value_re->user_id);
                $vendor_data = VendorDetailsModel::where('user_id',$value_re->user_id)->first();
                $reply[$key_re]->user_image = $vendor_details->user_image??'';
                $reply[$key_re]->company_name = $vendor_data->company_name??'';
               }
               
               $datamain[$key]->reply = $reply;
            }
     return $datamain;
  }
  public static function rating_list_by_services($where=[]){
        
    $datamain = Rating::select('ratings.id','name','user_id','service_id','rating','title','comment','ratings.created_at','order_id','users.user_image')
          ->leftjoin('users','users.id','=','ratings.user_id')
          ->whereIn('service_id',$where)->get();
         
          foreach ($datamain as $key => $value_rating) {
            $user = VendorDetailsModel::where('user_id',$value_rating->user_id)->first();
             
             if ($user && $user->logo) {
              $img = $user->logo;
             } else {
              $img = !$value_rating->user_image ? asset("storage/placeholder.png") : asset($value_rating->user_image);
             }
             $datamain[$key]->user_image = $img??'';
             $datamain[$key]->created_at = get_date_in_timezone($value_rating->created_at, 'Y-m-d H:i:s');
          }
   return $datamain;
}
}
