<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('jobs', 'reserved'))
        {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropColumn('reserved');
                $table->dropIndex(['queue', 'reserved', 'reserved_at']);
                $table->index('queue');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
