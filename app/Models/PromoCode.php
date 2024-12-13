<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $fillable = [
        'title',
        'promo_code',
        'description',
        'start_date',
        'end_date',
        'value',
        'type',
        'all_campaigns',
        'is_active'
    ];

    public function campaigns() {
        return $this->hasMany(PromoCodeCampaign::class, 'promo_code_id')->with('campaign');
    }
}
