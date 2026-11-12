<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_managers', function (Blueprint $table) {
            $table->integer('partners_count')->default(0)->after('status');
            $table->longText('partners_data')->nullable()->after('partners_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_managers', function (Blueprint $table) {
            $table->dropColumn(['partners_count', 'partners_data']);
        });
    }
};
