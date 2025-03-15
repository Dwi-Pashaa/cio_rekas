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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_id')->nullable()->references('id')->on('products')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('telp');
            $table->string('address');
            $table->string('limit');
            $table->enum('type', ['titip', 'beli']);
            $table->enum('status', ['terdaftar', 'tidak']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
