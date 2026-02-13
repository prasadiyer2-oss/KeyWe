<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CREATE MASTER TABLES
        
        // Master: BHK Types (1 BHK, 2 BHK...)
        Schema::create('bhk_types', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // "3 BHK"
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Master: Nearby Locations (Near work, Near metro...)
        Schema::create('nearby_locations', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // "Near Work"
            $table->string('icon')->nullable(); // To store icon class/url if needed
            $table->timestamps();
        });

        // Master: Move In Timeline (Immediately, 3-6 Months...)
        Schema::create('move_in_timelines', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // "3-6 Months"
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Master: Localities (Indiranagar, etc.)
        Schema::create('localities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->default('Bangalore');
            $table->timestamps();
        });

        // 2. CREATE PIVOT TABLES (The connectors)

        // Pivot: Preferences <-> BHK Types
        Schema::create('bhk_type_user_preference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_preference_id')->constrained()->onDelete('cascade');
            $table->foreignId('bhk_type_id')->constrained()->onDelete('cascade');
        });

        // Pivot: Preferences <-> Nearby Locations
        Schema::create('nearby_location_user_preference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_preference_id')->constrained()->onDelete('cascade');
            $table->foreignId('nearby_location_id')->constrained()->onDelete('cascade');
            // We specify a custom name for the FK constraint to avoid long name errors
        });

        // Pivot: Preferences <-> Move In Timelines
        Schema::create('move_in_timeline_user_preference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_preference_id')->constrained()->onDelete('cascade');
            $table->foreignId('move_in_timeline_id')->constrained()->onDelete('cascade');
        });

        // Pivot: Preferences <-> Localities
        Schema::create('locality_user_preference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_preference_id')->constrained()->onDelete('cascade');
            $table->foreignId('locality_id')->constrained()->onDelete('cascade');
        });

        // 3. CLEAN UP MAIN TABLE
        // We remove the old single-value columns since we now have pivots
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn(['locality', 'nearby_location', 'ready_to_move_by']);
        });

        // 4. SEED DEFAULT DATA
        $this->seedData();
    }

    private function seedData()
    {
        // Seed BHK Types
        DB::table('bhk_types')->insert([
            ['label' => '1 BHK', 'sort_order' => 1],
            ['label' => '2 BHK', 'sort_order' => 2],
            ['label' => '3 BHK', 'sort_order' => 3],
            ['label' => 'Villa / Plot', 'sort_order' => 4],
        ]);

        // Seed Nearby Locations
        DB::table('nearby_locations')->insert([
            ['label' => 'Near Work'],
            ['label' => 'Near School'],
            ['label' => 'Near Metro'],
            ['label' => 'Quiet Area'],
            ['label' => 'Investment Hotspot'],
        ]);

        // Seed Timelines
        DB::table('move_in_timelines')->insert([
            ['label' => 'Immediately', 'sort_order' => 1],
            ['label' => '3-6 Months', 'sort_order' => 2],
            ['label' => '6-12 Months', 'sort_order' => 3],
            ['label' => 'Just Exploring', 'sort_order' => 4],
        ]);
        
        // Seed Dummy Localities (You can import more later)
        DB::table('localities')->insert([
            ['name' => 'Indiranagar'],
            ['name' => 'HSR Layout'],
            ['name' => 'JP Nagar'],
            ['name' => 'Whitefield'],
            ['name' => 'Koramangala'],
        ]);
    }

    public function down(): void
    {
        // Drop Pivots First
        Schema::dropIfExists('locality_user_preference');
        Schema::dropIfExists('move_in_timeline_user_preference');
        Schema::dropIfExists('nearby_location_user_preference');
        Schema::dropIfExists('bhk_type_user_preference');
        
        // Drop Masters
        Schema::dropIfExists('localities');
        Schema::dropIfExists('move_in_timelines');
        Schema::dropIfExists('nearby_locations');
        Schema::dropIfExists('bhk_types');

        // Restore Columns (Optional, usually strict rollback isn't needed in dev)
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->string('locality')->nullable();
            $table->string('nearby_location')->nullable();
            $table->date('ready_to_move_by')->nullable();
        });
    }
};