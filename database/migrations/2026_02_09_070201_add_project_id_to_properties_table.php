<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Only add the column/key if it does NOT exist yet
            if (!Schema::hasColumn('properties', 'project_id')) {
                $table->foreignId('project_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('projects')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};