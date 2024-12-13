<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeCampaign extends Model
{
    use HasFactory;

    protected $table = 'promo_codes_campaigns';
    public $timestamps = false;

    protected $fillable = [
        'campaign_id', 'promo_code_id'
    ];

    public function campaign() {
        return $this->belongsTo(CampaignModel::class, 'campaign_id', 'campaigns_id');
    }
}
