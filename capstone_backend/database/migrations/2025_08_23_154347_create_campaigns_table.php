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
        Schema::create('campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('charity_id')->constrained();
            $t->string('title');
            $t->text('description')->nullable();
            $t->decimal('target_amount',12,2)->nullable();
            $t->timestamp('deadline_at')->nullable();
            $t->string('cover_image_path')->nullable();
            $t->enum('status',['draft','published','closed','archived'])->default('draft');
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
