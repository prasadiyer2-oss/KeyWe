<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <--- IMPORT THIS

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Rename columns using Raw SQL (Fixes MariaDB Syntax Error)
        // We use "CHANGE" instead of "RENAME COLUMN"
        
        
        

        if (Schema::hasColumn('properties', 'area_sqft')) {
            DB::statement("ALTER TABLE properties CHANGE area_sqft carpet_area INT NOT NULL");
        }

        // STEP 2: Drop Old Columns & Add New Ones
        Schema::table('properties', function (Blueprint $table) {
            // Drop unneeded columns
            // ✅ ADD THIS
            $table->foreignId('partner_id')->after('id')->constrained('users')->onDelete('cascade');
            if (Schema::hasColumn('properties', 'configuration')) {
                $table->dropColumn('configuration');
            }
            if (Schema::hasColumn('properties', 'status')) {
                $table->dropColumn('status');
            }

            // Modify existing types
            $table->bigInteger('price')->change();
            
            // Add new columns
            // We use 'after' to position them correctly, though it's optional
            if (!Schema::hasColumn('properties', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            
            if (!Schema::hasColumn('properties', 'bhk')) {
                $table->integer('bhk')->after('carpet_area');
            }
            
            if (!Schema::hasColumn('properties', 'property_type')) {
                $table->enum('property_type', ['Apartment', 'Villa', 'Plot', 'Studio'])->after('bhk');
            }
            
            if (!Schema::hasColumn('properties', 'location')) {
                $table->string('location', 255)->after('property_type');
            }
            
            if (!Schema::hasColumn('properties', 'handover_date')) {
                $table->date('handover_date')->nullable()->after('location');
            }
            
            if (!Schema::hasColumn('properties', 'financing_option')) {
                $table->enum('financing_option', ['Loan', 'Full Payment', 'Both'])->after('handover_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop the new columns
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'bhk',
                'property_type',
                'location',
                'handover_date',
                'financing_option'
            ]);
            
            // Re-add dropped columns (approximation)
            $table->string('configuration')->nullable();
            $table->string('status')->nullable();
        });

        // 2. Rename back using Raw SQL
        if (Schema::hasColumn('properties', 'partner_id')) {
            DB::statement("ALTER TABLE properties CHANGE partner_id project_id BIGINT UNSIGNED NOT NULL");
        }

        if (Schema::hasColumn('properties', 'carpet_area')) {
            DB::statement("ALTER TABLE properties CHANGE carpet_area area_sqft INT NOT NULL");
        }
    }
};