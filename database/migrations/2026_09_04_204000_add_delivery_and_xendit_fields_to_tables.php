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
        // 1. Tambah field delivery_fee dan xendit di tabel usahas
        Schema::table('usahas', function (Blueprint $table) {
            $table->double('delivery_fee')->default(0)->after('footer');
            $table->text('xendit_secret_key')->nullable()->after('qontak_template_id');
            $table->text('xendit_webhook_token')->nullable()->after('xendit_secret_key');
        });

        // 2. Tambah field delivery, payment method, dan xendit di tabel transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('delivery_type')->default('pickup')->after('qty'); // pickup / delivery
            $table->double('delivery_fee')->default(0)->after('delivery_type');
            $table->string('payment_method')->default('cash')->after('payment'); // cash / xendit / transfer
            $table->string('payment_status')->default('paid')->after('payment_method'); // paid / unpaid / pending
            $table->string('xendit_invoice_id')->nullable()->after('payment_status');
            $table->text('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
        });

        // 3. Tambah field wa_number di tabel branche
        Schema::table('branche', function (Blueprint $table) {
            $table->string('wa_number', 50)->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usahas', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'xendit_secret_key', 'xendit_webhook_token']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'delivery_fee',
                'payment_method',
                'payment_status',
                'xendit_invoice_id',
                'xendit_invoice_url'
            ]);
        });

        Schema::table('branche', function (Blueprint $table) {
            $table->dropColumn(['wa_number']);
        });
    }
};
