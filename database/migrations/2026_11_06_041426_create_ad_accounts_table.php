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
        Schema::create('ad_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_manager_id')->constrained()->cascadeOnDelete();
            $table->string('ad_account_id'); // act_123456789
            $table->string('name');
            $table->string('currency', 10)->default('VND');
            // account_status: 1=ACTIVE, 2=DISABLED, 3=UNSETTLED, 7=PENDING_RISK_REVIEW, 8=PENDING_SETTLEMENT, 9=IN_GRACE_PERIOD, 100=PENDING_CLOSURE, 101=CLOSED, 201=ANY_ACTIVE, 202=ANY_CLOSED
            $table->integer('account_status')->default(1);
            $table->decimal('amount_spent', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('spend_cap', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['business_manager_id', 'ad_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_accounts');
    }
};
