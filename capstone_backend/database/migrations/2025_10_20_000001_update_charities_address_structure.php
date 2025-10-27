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
            // Drop old address fields
            $table->dropColumn(['address', 'region', 'municipality']);
            
            // Add new Philippine address structure
            $table->string('street_address')->nullable()->after('contact_phone');
            $table->string('barangay')->nullable()->after('street_address');
            $table->string('city')->nullable()->after('barangay');
            $table->string('province')->nullable()->after('city');
            $table->string('region')->nullable()->after('province');
            $table->text('full_address')->nullable()->after('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charities', function (Blueprint $table) {
            // Drop new fields
            $table->dropColumn(['street_address', 'barangay', 'city', 'province', 'region', 'full_address']);
            
            // Restore old fields
            $table->text('address')->nullable();
            $table->string('region')->nullable();
            $table->string('municipality')->nullable();
        });
    }
};
