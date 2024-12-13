<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSocialToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_table', function (Blueprint $table) {
            $table->string('is_social')->default('0')->after('user_email_id');  // Adjust the 'after' if needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_table', function (Blueprint $table) {
            $table->dropColumn('is_social');
        });
    }
}
