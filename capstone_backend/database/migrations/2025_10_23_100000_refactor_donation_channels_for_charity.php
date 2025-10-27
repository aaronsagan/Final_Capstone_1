<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old campaign_id foreign key if it exists
        if (Schema::hasColumn('donation_channels', 'campaign_id')) {
            Schema::table('donation_channels', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            });
        }

        // Add charity_id if it doesn't exist
        if (!Schema::hasColumn('donation_channels', 'charity_id')) {
            Schema::table('donation_channels', function (Blueprint $table) {
                $table->foreignId('charity_id')->after('id')->constrained('charities')->onDelete('cascade');
                $table->index('charity_id');
            });
        }

        // Create pivot table for campaign-channel relationships if it doesn't exist
        if (!Schema::hasTable('campaign_donation_channel')) {
            Schema::create('campaign_donation_channel', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
                $table->foreignId('donation_channel_id')->constrained('donation_channels')->onDelete('cascade');
                $table->timestamps();

                // Prevent duplicate assignments
                $table->unique(['campaign_id', 'donation_channel_id']);
                $table->index('campaign_id');
                $table->index('donation_channel_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_donation_channel');

        Schema::table('donation_channels', function (Blueprint $table) {
            $table->dropForeign(['charity_id']);
            $table->dropColumn('charity_id');
            $table->foreignId('campaign_id')->after('id')->nullable()->constrained('campaigns')->onDelete('cascade');
        });
    }
};
