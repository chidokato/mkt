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
        Schema::create('business_managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bm_id')->nullable();
            // Lưu access token (được mã hóa tự động bằng Casts của Laravel)
            $table->text('access_token');
            $table->string('status')->default('active'); // active, disabled, expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_managers');
    }
};
