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
        Schema::create('page_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('batch_id', 36)->index();
            $table->foreignId('facebook_page_id')->nullable()->constrained('facebook_pages')->nullOnDelete();
            $table->string('page_id');
            $table->string('page_name');
            $table->string('post_type', 20)->default('text'); // 'text', 'link', 'photo'
            $table->text('message')->nullable();
            $table->text('link_url')->nullable();
            $table->unsignedSmallInteger('media_count')->default(0);
            $table->string('publish_mode', 20)->default('now'); // 'now', 'scheduled'
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('fb_post_id')->nullable();
            $table->text('permalink_url')->nullable();
            $table->string('status', 20)->default('published'); // 'published', 'scheduled', 'failed', 'cancelled'
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_posts');
    }
};
