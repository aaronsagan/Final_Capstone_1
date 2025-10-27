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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'sms_notifications_enabled')) {
                $table->boolean('sms_notifications_enabled')->default(true)->after('phone');
            }
            if (!Schema::hasColumn('users', 'sms_notification_types')) {
                $table->json('sms_notification_types')->nullable()->after('sms_notifications_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'sms_notifications_enabled', 'sms_notification_types']);
        });
    }
};
