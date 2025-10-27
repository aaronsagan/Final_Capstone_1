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
        Schema::create('update_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('update_id')->constrained('updates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            
            $table->index('update_id');
            $table->index('user_id');
            $table->index(['update_id', 'is_hidden', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_comments');
    }
};
