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
            $table->boolean('is_recurring')->default(false)->after('is_anonymous');
            $table->enum('recurring_type', ['weekly', 'monthly', 'quarterly', 'yearly'])->nullable()->after('is_recurring');
            $table->date('recurring_end_date')->nullable()->after('recurring_type');
            $table->timestamp('next_donation_date')->nullable()->after('recurring_end_date');
            $table->unsignedBigInteger('parent_donation_id')->nullable()->after('next_donation_date');
            $table->foreign('parent_donation_id')->references('id')->on('donations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['parent_donation_id']);
            $table->dropColumn(['is_recurring', 'recurring_type', 'recurring_end_date', 'next_donation_date', 'parent_donation_id']);
        });
    }
};
