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
        Schema::table('charities', function (Blueprint $table) {
            // Add description field if it doesn't exist
            if (!Schema::hasColumn('charities', 'description')) {
                $table->text('description')->nullable()->after('vision');
            }
            
            // Add simplified contact fields (for Edit Profile form)
            if (!Schema::hasColumn('charities', 'first_name')) {
                $table->string('first_name', 50)->nullable()->after('contact_phone');
            }
            if (!Schema::hasColumn('charities', 'middle_initial')) {
                $table->string('middle_initial', 1)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('charities', 'last_name')) {
                $table->string('last_name', 50)->nullable()->after('middle_initial');
            }
            
            // Add simplified address field (for Edit Profile form)
            if (!Schema::hasColumn('charities', 'address')) {
                $table->text('address')->nullable()->after('last_name');
            }
            
            // Add municipality field (for Edit Profile form - city/municipality)
            if (!Schema::hasColumn('charities', 'municipality')) {
                $table->string('municipality')->nullable()->after('region');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charities', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'first_name',
                'middle_initial',
                'last_name',
                'address',
                'municipality'
            ]);
        });
    }
};
