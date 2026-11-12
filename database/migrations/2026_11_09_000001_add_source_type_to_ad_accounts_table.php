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
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->string('source_type', 20)->default('owned')->after('spend_cap'); // 'owned', 'client', 'personal'
            $table->string('owner_business_id', 100)->nullable()->after('source_type');
            $table->string('owner_business_name', 255)->nullable()->after('owner_business_id');

            $table->index(['business_manager_id', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->dropIndex(['business_manager_id', 'source_type']);
            $table->dropColumn(['source_type', 'owner_business_id', 'owner_business_name']);
        });
    }
};
