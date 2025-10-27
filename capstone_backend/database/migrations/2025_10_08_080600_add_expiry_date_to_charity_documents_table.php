<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charity_documents', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('file_path');
            $table->boolean('expires')->default(false)->after('expiry_date');
            $table->date('renewal_reminder_sent_at')->nullable()->after('expires');
        });
    }

    public function down(): void
    {
        Schema::table('charity_documents', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'expires', 'renewal_reminder_sent_at']);
        });
    }
};
