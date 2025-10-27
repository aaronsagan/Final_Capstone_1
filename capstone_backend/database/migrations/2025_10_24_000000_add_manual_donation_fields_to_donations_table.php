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
        Schema::table('donations', function (Blueprint $table) {
            // For manual proof submissions
            $table->string('donor_name')->nullable()->after('donor_id');
            $table->string('donor_email')->nullable()->after('donor_name');
            $table->string('channel_used')->nullable()->after('proof_type');
            $table->string('reference_number')->nullable()->after('channel_used');
            $table->text('message')->nullable()->after('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['donor_name', 'donor_email', 'channel_used', 'reference_number', 'message']);
        });
    }
};
