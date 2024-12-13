<?php
namespace App\Models;
use App;
use Illuminate\Database\Eloquent\Model;

class ContactUsSetting extends Model
{
    protected  $fillable = ['title_en','title_ar','email','mobile','desc_en','desc_ar','location','latitude','longitude'];

    public function getTitleAttribute()
    {
        return $this->{'title_'.App::getLocale()};
    }

    public function getDescriptionAttribute()
    {
        return $this->{'desc_'.App::getLocale()};
    }
}
