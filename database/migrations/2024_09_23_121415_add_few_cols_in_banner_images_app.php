<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFewColsInBannerImagesApp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banner_images_app', function (Blueprint $table) {
            $table->bigInteger('product_id')->nullable();
            $table->bigInteger('product_attr_id')->nullable();
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
            $table->dropColumn('product_id');
            $table->dropColumn('product_attr_id');
        });
    }
}
