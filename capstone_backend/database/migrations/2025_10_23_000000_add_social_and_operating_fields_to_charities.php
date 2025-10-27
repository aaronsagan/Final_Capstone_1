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
            // Add operating hours field
            if (!Schema::hasColumn('charities', 'operating_hours')) {
                $table->string('operating_hours')->nullable()->after('address');
            }
            
            // Add social media URLs
            if (!Schema::hasColumn('charities', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('website');
            }
            if (!Schema::hasColumn('charities', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('facebook_url');
            }
            if (!Schema::hasColumn('charities', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('instagram_url');
            }
            if (!Schema::hasColumn('charities', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('twitter_url');
            }
            if (!Schema::hasColumn('charities', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('linkedin_url');
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
                'operating_hours',
                'facebook_url',
                'instagram_url',
                'twitter_url',
                'linkedin_url',
                'youtube_url'
            ]);
        });
    }
};
