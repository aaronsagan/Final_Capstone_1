<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('donations', function (Blueprint $t) 
        {
            $t->id();
            $t->foreignId('donor_id')->nullable()->constrained('users'); // null if public-anon
            $t->foreignId('charity_id')->constrained();
            $t->foreignId('campaign_id')->nullable()->constrained();
            $t->decimal('amount',12,2);
            $t->enum('purpose',['general','project','emergency'])->default('general');
            $t->boolean('is_anonymous')->default(false);
            $t->enum('status',['pending','completed','rejected'])->default('pending');
            $t->string('proof_path')->nullable();
            $t->string('proof_type')->nullable();
            $t->string('external_ref')->nullable();
            $t->string('receipt_no')->nullable();
            $t->timestamp('donated_at')->useCurrent();
            $t->timestamps();
            $t->index(['charity_id','campaign_id','status']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
