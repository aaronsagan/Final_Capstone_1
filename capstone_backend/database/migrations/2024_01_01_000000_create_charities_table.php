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
        Schema::create('charities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users');
            $t->string('name');
            $t->string('reg_no')->nullable();
            $t->string('tax_id')->nullable();
            $t->text('mission')->nullable();
            $t->text('vision')->nullable();
            $t->string('website')->nullable();
            $t->string('contact_email')->nullable();
            $t->string('contact_phone')->nullable();
            $t->string('logo_path')->nullable();
            $t->enum('verification_status',['pending','approved','rejected'])->default('pending');
            $t->timestamp('verified_at')->nullable();
            $t->text('verification_notes')->nullable();
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charities');
    }
};
