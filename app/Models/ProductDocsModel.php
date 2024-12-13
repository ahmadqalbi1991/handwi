<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDocsModel extends Model
{
    //
    protected $table = "product_docs";
    protected $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'product_id',
        'title',
        'doc_path',
    ];
    
}
