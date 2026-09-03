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
        Schema::table('usahas', function (Blueprint $table) {
            $table->boolean('enable_wa_notification')->default(false)->after('footer');
            $table->boolean('enable_email_notification')->default(false)->after('enable_wa_notification');
            $table->string('admin_wa_number', 50)->nullable()->after('enable_email_notification');
            $table->text('qontak_token')->nullable()->after('admin_wa_number');
            $table->string('qontak_channel_id', 100)->nullable()->after('qontak_token');
            $table->string('qontak_template_id', 100)->nullable()->after('qontak_channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usahas', function (Blueprint $table) {
            $table->dropColumn([
                'enable_wa_notification',
                'enable_email_notification',
                'admin_wa_number',
                'qontak_token',
                'qontak_channel_id',
                'qontak_template_id',
            ]);
        });
    }
};
