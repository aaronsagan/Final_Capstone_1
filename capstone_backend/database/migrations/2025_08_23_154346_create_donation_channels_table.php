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
        Schema::create('donation_channels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('charity_id')->constrained();
            $t->enum('type',['gcash','paypal','bank','other']);
            $t->string('label');
            $t->json('details');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_channels');
    }
};
