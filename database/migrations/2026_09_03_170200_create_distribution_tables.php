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
        Schema::create('distribution_stocks', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['utama', 'cabang'])->default('utama');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->index(['type', 'user_id', 'product_name']);
        });

        Schema::create('distribution_histories', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->enum('type', ['admin_to_utama', 'utama_to_cabang', 'cabang_to_branch']);
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_branch_id')->nullable()->constrained('branche')->nullOnDelete();
            $table->string('product_name');
            $table->integer('qty');
            $table->integer('stock_before')->nullable();
            $table->integer('stock_after')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'sender_id']);
            $table->index(['type', 'receiver_id']);
            $table->index(['type', 'target_branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_histories');
        Schema::dropIfExists('distribution_stocks');
    }
};
