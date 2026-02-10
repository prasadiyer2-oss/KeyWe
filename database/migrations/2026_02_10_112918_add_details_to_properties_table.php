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
            // Using a string for status allows you to add "New Launch" later without changing DB enums
            $table->string('construction_status')->nullable()->after('total_floors')
                  ->comment('Ready to Move, Under Construction, Pre-Launch');

            $table->date('possession_date')->nullable()->after('construction_status')
                  ->comment('Date when possession will be given');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'floor_number', 
                'total_floors', 
                'construction_status', 
                'possession_date'
            ]);
        });
    }
};