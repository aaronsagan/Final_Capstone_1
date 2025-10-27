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
        Schema::create('charity_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('charity_id')->constrained();
            $t->enum('doc_type',['registration','tax','bylaws','audit','other']);
            $t->string('file_path');
            $t->string('sha256',64)->nullable();
            $t->foreignId('uploaded_by')->constrained('users');
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charity_documents');
    }
};
