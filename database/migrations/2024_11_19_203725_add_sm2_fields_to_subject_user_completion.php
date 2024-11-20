<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('subject_user_completion', function (Blueprint $table) {
            $table->integer('repetition_count')->default(0);
            $table->float('easiness_factor')->default(2.5);
            $table->integer('interval')->default(1);
            $table->timestamp('next_review_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('subject_user_completion', function (Blueprint $table) {
            $table->dropColumn([
                'repetition_count',
                'easiness_factor',
                'interval',
                'next_review_at'
            ]);
        });
    }
};