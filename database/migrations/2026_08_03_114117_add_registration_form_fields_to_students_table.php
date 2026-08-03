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
        Schema::table('students', function (Blueprint $table) {
            $table->string('mother_maiden_name')->nullable()->after('date_of_birth');
            $table->string('sex')->nullable()->after('mother_maiden_name');
            $table->string('state_of_origin')->nullable()->after('sex');
            $table->string('local_government_area')->nullable()->after('state_of_origin');
            $table->string('occupation')->nullable()->after('local_government_area');
            $table->string('next_of_kin_name')->nullable()->after('address');
            $table->string('next_of_kin_address')->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_phone')->nullable()->after('next_of_kin_address');
            $table->string('next_of_kin_email')->nullable()->after('next_of_kin_phone');
            $table->string('vehicle_class')->nullable()->after('course_type');
            $table->boolean('has_driving_experience')->nullable()->after('vehicle_class');
            $table->boolean('requires_classes')->nullable()->after('has_driving_experience');
            $table->string('referral_source')->nullable()->after('requires_classes');
            $table->string('referral_source_other')->nullable()->after('referral_source');
            $table->string('photo_path')->nullable()->after('referral_source_other');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'mother_maiden_name',
                'sex',
                'state_of_origin',
                'local_government_area',
                'occupation',
                'next_of_kin_name',
                'next_of_kin_address',
                'next_of_kin_phone',
                'next_of_kin_email',
                'vehicle_class',
                'has_driving_experience',
                'requires_classes',
                'referral_source',
                'referral_source_other',
                'photo_path',
            ]);
        });
    }
};
