<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->enum('reporter_role', ['donor', 'charity_admin']);
            
            // What is being reported
            $table->enum('reported_entity_type', ['user', 'charity', 'campaign', 'donation']);
            $table->unsignedBigInteger('reported_entity_id');
            
            // Report details
            $table->enum('reason', [
                'fraud',
                'fake_proof',
                'inappropriate_content',
                'scam',
                'fake_charity',
                'misuse_of_funds',
                'spam',
                'harassment',
                'other'
            ]);
            $table->text('description');
            $table->string('evidence_path')->nullable(); // File upload
            
            // Admin review
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Action taken
            $table->enum('action_taken', ['none', 'warned', 'suspended', 'deleted', 'contacted'])->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['reported_entity_type', 'reported_entity_id']);
            $table->index('status');
            $table->index('reporter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
