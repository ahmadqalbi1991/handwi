<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColsInFaqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('faq', function (Blueprint $table) {
            $table->dropColumn(['faq_title_arabic', 'faq_description_arabic', 'updated_at', 'meta_title', 'meta_keyword', 'meta_description']);
        });
    }
}
