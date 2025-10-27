<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN, so we skip this for SQLite
        // The original table already has doc_type as string in SQLite
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE charity_documents MODIFY COLUMN doc_type VARCHAR(255)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to enum (only for non-SQLite databases)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE charity_documents MODIFY COLUMN doc_type ENUM('registration','tax','bylaws','audit','other')");
        }
    }
};
