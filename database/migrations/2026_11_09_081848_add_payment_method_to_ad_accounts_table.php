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
            $table->string('payment_method_display', 100)->nullable()->after('spend_cap');
            $table->string('card_last_four', 10)->nullable()->after('payment_method_display');
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
            $table->dropColumn(['payment_method_display', 'card_last_four']);
        });
    }
};
