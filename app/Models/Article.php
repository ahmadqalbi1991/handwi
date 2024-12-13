<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;

class Article extends Model
{

    protected $table = 'articles';

    public static function getArticle($type_id, $country_id, $language) {
        $result = DB::table('articles as a')
            ->select('*')
            ->join('article_type as t', 'a.articles_type_id', '=', 't.article_type_id')
            ->where('a.articles_type_id', $type_id)
            ->where('a.country_id', $country_id)
            ->where('a.articles_language_code', $language)
            ->first();

        return json_decode(json_encode($result), true);
    }
}
