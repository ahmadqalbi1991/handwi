<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('promo_code')->nullable();
            $table->longText('description')->nullable();
            $table->float('value')->default(0)->nullable();
            $table->enum('type', ['percentage', 'fixed'])->nullable();
            $table->boolean('all_campaigns')->default(0);
            $table->boolean('is_active')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_codes');
    }
}
