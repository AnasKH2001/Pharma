<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name', 500);
            $table->string('manufacturer', 500);
            $table->text('generic_name');  // TEXT for long descriptions
            $table->string('dosage', 255);
            $table->string('form', 255);
            $table->timestamps();
            
            // Add unique constraint
            $table->unique(['brand_name', 'manufacturer', 'generic_name', 'dosage', 'form'], 'medicines_unique_key');
            
            $table->index('generic_name');
            $table->index('brand_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};