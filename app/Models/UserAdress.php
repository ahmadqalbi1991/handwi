<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdress extends Model
{
    use HasFactory;
    protected $table = "user_address";
    public static function get_address_list($user_id)
    {

        $list = UserAdress::where(['status' => 1, 'user_id' => $user_id])->orderBy('is_default','desc')->orderBy('id', 'DESC')->get();
        foreach ($list as $key => $row) {
            // if($key == 0)
            // {
            //      $list[$key]->is_default = 1;
            // }
            $list[$key]->country_name = ($row->country_id ?CountryModel::where('id', $row->country_id)->value('name') :'') ?? '';
            // $list[$key]->state_name = (States::where('id', $row->state_id)->value('name')) ?? '';
            $list[$key]->city_name = ($row->city_id ?Cities::where('id', $row->city_id)->value('name') :'') ?? '';
            $list[$key]->area_name = ($row->area_id ?Area::where('id', $row->area_id)->value('name') :'') ?? '';
        }

        return $list->count() ? convert_all_elements_to_string($list) : [];
    }
    public static function get_address_details($address_id)
    {
        $adress = UserAdress::where(['user_address.id' => $address_id])
        // ->select('id', 'full_name', 'address', 'land_mark','building_name','latitude','longitude','location','is_default','country_id','city_id','area_id')
            ->first();
        if($adress){    
            $adress->country_name = ($adress->country_id ?CountryModel::where('id', $adress->country_id)->value('name') :'') ?? '';
            $adress->city_name = ($adress->city_id ?Cities::where('id', $adress->city_id)->value('name') :'') ?? '';
            $adress->area_name = ($adress->area_id ?Area::where('id', $adress->area_id)->value('name') :'') ?? '';
             $adress = convert_all_elements_to_string($adress);
        }
        return $adress;//convert_all_elements_to_string($adress);
    }

    public static function get_user_default_address($userid)
    {

        $adress = UserAdress::where(['status' => 1, 'user_id' => $userid])
        // ->select('id', 'full_name', 'address', 'land_mark','building_name','latitude','longitude','location','is_default')
        //->select('user_address.*', 'country.name as country_name', 'states.name as state_name', 'cities.name as city_name')
            // ->leftjoin('country', 'country.id', 'user_address.country_id')
            // ->leftjoin('states', 'states.id', 'user_address.state_id')
            // ->leftjoin('cities', 'cities.id', 'user_address.city_id')
            ->where('is_default', 1)->first();
        if (!$adress) {
            $adress = UserAdress::where(['status' => 1, 'user_id' => $userid])
            // ->select('id', 'full_name', 'address', 'land_mark','building_name','latitude','longitude','location','is_default')
                // ->leftjoin('country', 'country.id', 'user_address.country_id')
                // ->leftjoin('states', 'states.id', 'user_address.state_id')
                // ->leftjoin('cities', 'cities.id', 'user_address.city_id')
                ->orderBy('id', 'DESC')->first();
        }
        if($adress){
            $adress->country_name = ($adress->country_id ?CountryModel::where('id', $adress->country_id)->value('name') :'') ?? '';
            $adress->city_name = ($adress->city_id ?Cities::where('id', $adress->city_id)->value('name') :'') ?? '';
            $adress->area_name = ($adress->area_id ?Area::where('id', $adress->area_id)->value('name') :'') ?? '';   
            $adress = convert_all_elements_to_string($adress);
        }
        return $adress ?? new UserAdress();
    }

}
