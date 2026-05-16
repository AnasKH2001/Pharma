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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('order_date');
            $table->enum('status', ['pending', 'assigned', 'done', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index('pharmacy_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
