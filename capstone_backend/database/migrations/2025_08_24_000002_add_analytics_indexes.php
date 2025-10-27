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
        Schema::table('campaigns', function (Blueprint $table) {
            // Add indexes for analytics queries
            $table->index('campaign_type', 'idx_campaigns_campaign_type');
            $table->index('created_at', 'idx_campaigns_created_at');
            $table->index('region', 'idx_campaigns_region');
            $table->index('province', 'idx_campaigns_province');
            $table->index('city', 'idx_campaigns_city');
            $table->index(['campaign_type', 'status'], 'idx_campaigns_type_status');
            $table->index(['region', 'status'], 'idx_campaigns_region_status');
            $table->index(['created_at', 'status'], 'idx_campaigns_created_status');
        });

        Schema::table('donations', function (Blueprint $table) {
            // Add indexes for analytics queries
            $table->index('created_at', 'idx_donations_created_at');
            $table->index('donated_at', 'idx_donations_donated_at');
            $table->index(['status', 'created_at'], 'idx_donations_status_created');
            $table->index(['charity_id', 'created_at'], 'idx_donations_charity_created');
            $table->index(['campaign_id', 'status', 'created_at'], 'idx_donations_campaign_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex('idx_campaigns_campaign_type');
            $table->dropIndex('idx_campaigns_created_at');
            $table->dropIndex('idx_campaigns_region');
            $table->dropIndex('idx_campaigns_province');
            $table->dropIndex('idx_campaigns_city');
            $table->dropIndex('idx_campaigns_type_status');
            $table->dropIndex('idx_campaigns_region_status');
            $table->dropIndex('idx_campaigns_created_status');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_donations_created_at');
            $table->dropIndex('idx_donations_donated_at');
            $table->dropIndex('idx_donations_status_created');
            $table->dropIndex('idx_donations_charity_created');
            $table->dropIndex('idx_donations_campaign_status_created');
        });
    }
};
