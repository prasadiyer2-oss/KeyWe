<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MASTER: Budget Ranges (e.g. "50L - 1Cr")
        Schema::create('budget_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('label'); 
            $table->bigInteger('min_price'); 
            $table->bigInteger('max_price'); 
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. MASTER: Property Types (e.g. "Villa", "Resale")
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. MAIN: User Preferences Table
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Link to Budget (One option)
            $table->foreignId('budget_range_id')->nullable()->constrained('budget_ranges')->nullOnDelete();
            
            // Location & Date
            $table->string('locality')->nullable(); // Text location
            $table->decimal('latitude', 10, 8)->nullable();   // For "Nearby" search
            $table->decimal('longitude', 11, 8)->nullable();  // For "Nearby" search
            $table->integer('search_radius_km')->default(10); // How far to look?
            $table->date('ready_to_move_by')->nullable();     // "I need it by June 2026"
            
            $table->timestamps();
        });

        // 4. PIVOT: Links Preference to Multiple Types (User wants "Villa" AND "Plot")
        Schema::create('property_type_user_preference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_preference_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_type_id')->constrained('property_types')->onDelete('cascade');
        });

        // --- SEED DEFAULT DATA (So it works immediately) ---
        
        // Default Budget Ranges
        DB::table('budget_ranges')->insert([
            ['label' => 'Under ₹50 Lakhs', 'min_price' => 0, 'max_price' => 5000000, 'sort_order' => 1],
            ['label' => '₹50L - ₹1 Cr', 'min_price' => 5000000, 'max_price' => 10000000, 'sort_order' => 2],
            ['label' => '₹1 Cr - ₹3 Cr', 'min_price' => 10000000, 'max_price' => 30000000, 'sort_order' => 3],
            ['label' => 'Above ₹3 Cr', 'min_price' => 30000000, 'max_price' => 9999999999, 'sort_order' => 4],
        ]);

        // Default Property Types
        DB::table('property_types')->insert([
            ['name' => 'New Launch', 'slug' => 'new-launch', 'is_active' => true],
            ['name' => 'Resale', 'slug' => 'resale', 'is_active' => true],
            ['name' => 'Apartment', 'slug' => 'apartment', 'is_active' => true],
            ['name' => 'Villa', 'slug' => 'villa', 'is_active' => true],
            ['name' => 'Plot', 'slug' => 'plot', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('property_type_user_preference');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('property_types');
        Schema::dropIfExists('budget_ranges');
    }
};