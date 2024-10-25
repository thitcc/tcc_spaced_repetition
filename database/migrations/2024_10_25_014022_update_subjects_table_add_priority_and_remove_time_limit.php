<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('priority')->default(1);
            $table->dropColumn('time_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('time_limit')->nullable();
            $table->dropColumn('priority');
        });
    }
};
