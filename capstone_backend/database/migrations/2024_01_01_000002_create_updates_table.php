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
        Schema::create('updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->constrained('charities')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('updates')->onDelete('cascade');
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamps();
            
            $table->index('charity_id');
            $table->index('parent_id');
            $table->index(['charity_id', 'is_pinned', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updates');
    }
};
