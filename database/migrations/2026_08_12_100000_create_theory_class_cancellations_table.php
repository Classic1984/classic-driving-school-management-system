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
        Schema::create('theory_class_cancellations', function (Blueprint $table) {
            $table->id();
            // One row per cancelled class date - the weekly reminder checks
            // this before sending, so a Director can cancel a specific
            // Thursday without touching the recurring schedule itself.
            $table->date('class_date')->unique();
            $table->string('reason')->nullable();
            $table->foreignId('cancelled_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theory_class_cancellations');
    }
};
