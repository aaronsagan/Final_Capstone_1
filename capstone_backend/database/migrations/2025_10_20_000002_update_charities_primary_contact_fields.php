<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('charities', function (Blueprint $table) {
            // Add new structured contact fields (only if they don't exist)
            if (!Schema::hasColumn('charities', 'primary_first_name')) {
                $table->string('primary_first_name', 50)->nullable();
            }
            if (!Schema::hasColumn('charities', 'primary_middle_initial')) {
                $table->string('primary_middle_initial', 1)->nullable();
            }
            if (!Schema::hasColumn('charities', 'primary_last_name')) {
                $table->string('primary_last_name', 50)->nullable();
            }
            if (!Schema::hasColumn('charities', 'primary_position')) {
                $table->string('primary_position', 100)->nullable();
            }
            if (!Schema::hasColumn('charities', 'primary_email')) {
                $table->string('primary_email', 100)->nullable();
            }
            if (!Schema::hasColumn('charities', 'primary_phone')) {
                $table->string('primary_phone', 15)->nullable();
            }
        });

        // Migrate existing data from old fields to new fields (only if old columns exist)
        if (Schema::hasColumn('charities', 'contact_person_name')) {
            DB::statement("
                UPDATE charities 
                SET 
                    primary_first_name = SUBSTRING_INDEX(contact_person_name, ' ', 1),
                    primary_last_name = SUBSTRING_INDEX(contact_person_name, ' ', -1),
                    primary_email = contact_email,
                    primary_phone = contact_phone
                WHERE contact_person_name IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charities', function (Blueprint $table) {
            $table->dropColumn([
                'primary_first_name',
                'primary_middle_initial',
                'primary_last_name',
                'primary_position',
                'primary_email',
                'primary_phone'
            ]);
        });
    }
};
