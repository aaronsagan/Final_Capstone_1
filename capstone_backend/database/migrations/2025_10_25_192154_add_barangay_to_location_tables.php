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
        // Update charities table - make all location fields required
        if (Schema::hasTable('charities')) {
            Schema::table('charities', function (Blueprint $table) {
                // Add barangay if it doesn't exist, or update it to be non-nullable
                if (!Schema::hasColumn('charities', 'barangay')) {
                    $table->string('barangay')->after('municipality')->nullable();
                }
                
                // Make location fields non-nullable after giving time to populate
                // (Run a data migration script first if you have existing records)
            });
        }

        // Update users table (for donors)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'barangay')) {
                    $table->string('barangay')->after('address')->nullable();
                }
                
                // Add structured location fields if they don't exist
                if (!Schema::hasColumn('users', 'region')) {
                    $table->string('region')->nullable();
                }
                if (!Schema::hasColumn('users', 'province')) {
                    $table->string('province')->nullable();
                }
                if (!Schema::hasColumn('users', 'city')) {
                    $table->string('city')->nullable();
                }
            });
        }

        // Campaigns table already has region, province, city, barangay from previous migration
        // Just ensure barangay is present
        if (Schema::hasTable('campaigns')) {
            if (!Schema::hasColumn('campaigns', 'barangay')) {
                Schema::table('campaigns', function (Blueprint $table) {
                    $table->string('barangay')->nullable()->after('city');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('charities')) {
            Schema::table('charities', function (Blueprint $table) {
                $table->dropColumn('barangay');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'barangay')) {
                    $table->dropColumn(['barangay', 'region', 'province', 'city']);
                }
            });
        }

        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) {
                if (Schema::hasColumn('campaigns', 'barangay')) {
                    $table->dropColumn('barangay');
                }
            });
        }
    }
};
