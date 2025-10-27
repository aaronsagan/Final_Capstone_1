<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            
            // Action details
            $table->enum('action_type', [
                'approve_charity',
                'reject_charity',
                'suspend_user',
                'activate_user',
                'delete_user',
                'review_report',
                'delete_campaign',
                'delete_post',
                'update_settings',
                'export_data',
                'backup_data',
                'other'
            ]);
            
            // Target entity
            $table->string('target_type')->nullable(); // User, Charity, Campaign, Report, etc.
            $table->unsignedBigInteger('target_id')->nullable();
            
            // Detailed information
            $table->json('details')->nullable(); // Store before/after values, reasons, etc.
            $table->text('notes')->nullable();
            
            // Request metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('admin_id');
            $table->index('action_type');
            $table->index(['target_type', 'target_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};
