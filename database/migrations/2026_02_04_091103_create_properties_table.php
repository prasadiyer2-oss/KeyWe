<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            
            // Parent Relationship (FR-L-01: Projects with units)
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            // Unit Identifiers
            $table->string('title'); // e.g. "Tower A - 101" or "3BHK Premium"
            $table->string('configuration'); // e.g. "2BHK", "3BHK" (Searchable facet FR-UD-01)
            
            // Specifications
            $table->integer('area_sqft'); // Carpet Area
            $table->decimal('price', 15, 2); // Price supports large figures (e.g. 1,50,00,000)
            
            // Inventory Management
            $table->enum('status', ['Available', 'Sold', 'Reserved'])->default('Available');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
