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
        Schema::table('donation_channels', function (Blueprint $table) {
            // Add campaign_id to link donation channels to specific campaigns
            if (!Schema::hasColumn('donation_channels', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->after('charity_id')->constrained('campaigns')->onDelete('cascade');
            }
            
            // Update type enum to include more options
            if (Schema::hasColumn('donation_channels', 'type')) {
                $table->dropColumn('type');
            }
            $table->enum('type', ['gcash', 'maya', 'paymaya', 'paypal', 'bank', 'bank_transfer', 'ewallet', 'other'])->after('campaign_id')->default('gcash');
            
            // Add fields for QR code and additional details
            if (!Schema::hasColumn('donation_channels', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('details');
            }
            
            if (!Schema::hasColumn('donation_channels', 'account_name')) {
                $table->string('account_name')->nullable()->after('label');
            }
            
            if (!Schema::hasColumn('donation_channels', 'account_number')) {
                $table->string('account_number')->nullable()->after('account_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_channels', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn(['campaign_id', 'qr_code_path', 'account_name', 'account_number']);
        });
    }
};
