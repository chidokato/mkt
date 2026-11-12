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
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->timestamp('last_post_at')->nullable()->after('is_hot');
            $table->text('last_post_url')->nullable()->after('last_post_at');
            $table->timestamp('last_post_synced_at')->nullable()->after('last_post_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->dropColumn(['last_post_at', 'last_post_url', 'last_post_synced_at']);
        });
    }
};
