<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('charity_posts')) {
            Schema::create('charity_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('charity_id')->constrained('charities')->onDelete('cascade');
                $table->string('title');
                $table->text('content');
                $table->string('image_path')->nullable();
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('charity_posts');
    }
};
