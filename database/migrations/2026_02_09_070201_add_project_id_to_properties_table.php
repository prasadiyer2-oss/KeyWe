<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('properties', function (Blueprint $table) {
                // Only try to add if column is missing
                if (!Schema::hasColumn('properties', 'project_id')) {
                    $table->foreignId('project_id')
                          ->nullable()
                          ->after('id')
                          ->constrained('projects')
                          ->onDelete('cascade');
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Error Code 1061 = Duplicate key name
            // If we get this error, it means the setup is already done, so we ignore it.
            if ($e->errorInfo[1] === 1061) {
                return;
            }
            // If it's any other error, we still want to see it
            throw $e;
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};