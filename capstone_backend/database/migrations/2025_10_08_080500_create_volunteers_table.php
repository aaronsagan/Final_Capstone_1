<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade'); // Optional: specific campaign
            
            // Volunteer details
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            
            // Role and skills
            $table->enum('role', [
                'field_worker',
                'coordinator',
                'driver',
                'medical_staff',
                'teacher',
                'fundraiser',
                'social_media',
                'photographer',
                'translator',
                'other'
            ])->default('field_worker');
            $table->text('skills')->nullable(); // JSON or text description
            $table->text('experience')->nullable();
            
            // Status and availability
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->json('availability')->nullable(); // Days/hours available
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            
            // Emergency contact
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('charity_id');
            $table->index('campaign_id');
            $table->index('status');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
