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
        Schema::create('fund_usage_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('charity_id')->constrained();
            $t->foreignId('campaign_id')->nullable()->constrained();
            $t->decimal('amount',12,2);
            $t->enum('category',['supplies','staffing','transport','operations','other'])->default('other');
            $t->text('description')->nullable();
            $t->timestamp('spent_at')->useCurrent();
            $t->string('attachment_path')->nullable();
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_usage_logs');
    }
};
