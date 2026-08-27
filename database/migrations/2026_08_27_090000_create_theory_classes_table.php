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
        Schema::create('theory_classes', function (Blueprint $table) {
            $table->id();
            // One row per class held - the weekly reminder auto-creates
            // today's row (unless cancelled), so a roster always exists by
            // the time class starts.
            $table->date('class_date')->unique();
            $table->time('start_time')->nullable();
            $table->string('topic')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theory_classes');
    }
};
