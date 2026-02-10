<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. The main Filter category (e.g., "Budget", "BHK", "Possession Date")
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Budget Range"
            $table->string('slug')->unique(); // e.g., "budget-range"
            $table->string('type')->default('select'); // e.g., 'select', 'range', 'checkbox'
            $table->timestamps();
        });

        // 2. The specific options (e.g., "1Cr - 2Cr", "2 BHK", "Ready to Move")
        Schema::create('filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_id')->constrained()->onDelete('cascade');
            $table->string('label'); // Display text: "1 Cr - 2 Cr"
            $table->string('value'); // Internal value: "10000000-20000000"
            $table->timestamps();
        });

        // 3. Many-to-Many Pivot: A PROPERTY (Unit) can have multiple filter options
        // CHANGED: 'project_id' -> 'property_id' to match your properties table.
        Schema::create('property_filter_option', function (Blueprint $table) {
            $table->id();
            
            // This links to your existing 'properties' table
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            
            // This links to the specific option (e.g., "2 BHK")
            $table->foreignId('filter_option_id')->constrained('filter_options')->onDelete('cascade');
            
            // Optional: Index for faster filtering queries
            $table->index(['property_id', 'filter_option_id']); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_filter_option');
        Schema::dropIfExists('filter_options');
        Schema::dropIfExists('filters');
    }
};