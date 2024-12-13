<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceInclude extends Model
{
    use HasFactory;
    protected $table = "service_include";
    protected $primaryKey = "id";
    protected $guarded = [];

    protected $fillable = ['title', 'description', 'icon', 'service_id'];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'icon' => 'string',
    ];

    public function getIconAttribute($value)
    {
        return get_uploaded_image_url($value, 'service_image_upload_dir');
    }
}