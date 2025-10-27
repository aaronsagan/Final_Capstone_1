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
            $table->string('legal_trading_name')->nullable()->after('name');
            $table->text('address')->nullable()->after('contact_phone');
            $table->string('region')->nullable()->after('address');
            $table->string('municipality')->nullable()->after('region');
            $table->string('category')->nullable()->after('municipality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charities', function (Blueprint $table) {
            $table->dropColumn(['legal_trading_name', 'address', 'region', 'municipality', 'category']);
        });
    }
};
