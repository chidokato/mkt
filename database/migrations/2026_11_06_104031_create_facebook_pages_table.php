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
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_manager_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('page_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->json('category_list')->nullable();
            $table->text('link')->nullable();
            $table->text('picture_url')->nullable();
            $table->text('access_token')->nullable();
            $table->json('tasks')->nullable();
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedBigInteger('fan_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->string('verification_status')->default('not_verified');
            $table->string('source_type')->default('owned'); // 'owned', 'client', 'personal'
            $table->timestamps();

            $table->index(['user_id', 'page_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_pages');
    }
};
