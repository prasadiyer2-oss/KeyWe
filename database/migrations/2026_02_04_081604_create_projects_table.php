<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Link to the Builder (User)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            // FR-L-01: Basic Details
            $table->string('name');
            $table->string('location'); // Could be a relationship to a 'locations' table in the future
            $table->string('rera_number')->unique()->nullable();
            $table->string('project_type'); // e.g., 'residential', 'commercial'
            
            // FR-D-01 & FR-L-02: Status & Verification
            $table->enum('status', ['Ongoing', 'Completed', 'Upcoming'])->default('Upcoming');
            $table->enum('verification_status', ['Draft', 'Pending', 'Verified', 'Rejected'])->default('Draft');
            
            // Analytics & Data
            $table->integer('total_units')->default(0);
            $table->integer('views_count')->default(0); // FR-D-03: Analytics
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
