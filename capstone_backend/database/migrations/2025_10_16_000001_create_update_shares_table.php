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
        Schema::create('update_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('update_id')->constrained('updates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('platform')->nullable(); // 'facebook', 'twitter', 'copy_link', etc.
            $table->timestamps();
            
            $table->index('update_id');
            $table->index('user_id');
            $table->index(['update_id', 'user_id']);
        });

        // Add shares_count column to updates table
        Schema::table('updates', function (Blueprint $table) {
            $table->integer('shares_count')->default(0)->after('comments_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('updates', function (Blueprint $table) {
            $table->dropColumn('shares_count');
        });
        
        Schema::dropIfExists('update_shares');
    }
};
