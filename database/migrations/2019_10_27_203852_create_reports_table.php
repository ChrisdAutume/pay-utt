<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('service_id');
            $table->integer('amount');
            $table->dateTime('validated_at')->default(\Carbon\Carbon::now());
            $table->timestamps();
        });

        Schema::table('transactions' , function (Blueprint $table) {
            $table->bigInteger('report_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions' , function (Blueprint $table) {
            $table->dropColumn(['report_id']);
        });
        Schema::dropIfExists('reports');
    }
}
