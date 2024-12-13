<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInBannerImagesAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banner_images_app', function (Blueprint $table) {
            $table->dropColumn(['bi_target', 'bi_url', 'bi_language_code', 'bi_created_date', 'bi_type', 'bi_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banner_images_app', function (Blueprint $table) {
            //
        });
    }
}
