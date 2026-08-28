<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('theory_classes', function (Blueprint $table) {
            // The lecture material (slides, notes, handout) an instructor
            // uploads for this class - materials_original_name is kept
            // separately since the stored path is a generated filename,
            // not something worth showing to a person.
            $table->string('materials_path')->nullable()->after('notes');
            $table->string('materials_original_name')->nullable()->after('materials_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theory_classes', function (Blueprint $table) {
            $table->dropColumn(['materials_path', 'materials_original_name']);
        });
    }
};
