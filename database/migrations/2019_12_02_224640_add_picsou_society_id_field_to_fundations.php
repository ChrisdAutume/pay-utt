<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPicsouSocietyIdFieldToFundations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fundations', function (Blueprint $table) {
            $table->integer('picsou_society_id')->nullable()->before('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fundations', function (Blueprint $table) {
            $table->dropColumn('picsou_society_id');
        });
    }
}
