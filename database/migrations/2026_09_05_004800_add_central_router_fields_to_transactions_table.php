<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'xendit_external_id')) {
                $table->string('xendit_external_id')->nullable()->after('payment_status')->index();
            }
            if (!Schema::hasColumn('transactions', 'payment_channel')) {
                $table->string('payment_channel')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'xendit_external_id')) {
                $table->dropColumn('xendit_external_id');
            }
            if (Schema::hasColumn('transactions', 'payment_channel')) {
                $table->dropColumn('payment_channel');
            }
            if (Schema::hasColumn('transactions', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
