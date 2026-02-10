<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            
            // 1. Floor Details
            $table->integer('floor_number')->nullable()->after('price')
                  ->comment('The specific floor this unit is on (e.g. 5)');
            
            $table->integer('total_floors')->nullable()->after('floor_number')
                  ->comment('Total floors in the building (e.g. 5 out of 22)');

            // 2. Possession & Construction
            // Using a string allows for flexible statuses like "New Launch"
            $table->string('construction_status')->nullable()->after('total_floors')
                  ->comment('Ready to Move, Under Construction, Pre-Launch');

            $table->date('possession_date')->nullable()->after('construction_status')
                  ->comment('Date when possession will be given');

            // 3. Modify BHK from Integer to String (e.g. allow "3.5 BHK" or "Studio")
            $table->string('bhk')->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Revert BHK back to integer
            $table->integer('bhk')->change();

            // Drop the new columns
            $table->dropColumn([
                'floor_number', 
                'total_floors', 
                'construction_status', 
                'possession_date'
            ]);
        });
    }
};