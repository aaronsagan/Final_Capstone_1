<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Update;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupTrashedUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:trashed-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete trashed updates older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        $updates = Update::onlyTrashed()
            ->where('deleted_at', '<=', $thirtyDaysAgo)
            ->get();

        $count = 0;

        foreach ($updates as $update) {
            // Delete associated media files
            if ($update->media_urls) {
                foreach ($update->media_urls as $mediaUrl) {
                    try {
                        Storage::disk('public')->delete($mediaUrl);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete media file: {$mediaUrl}");
                    }
                }
            }

            // Permanently delete the update
            $update->forceDelete();
            $count++;
        }

        $this->info("Permanently deleted {$count} trashed updates older than 30 days.");
        Log::info("Cleanup: Permanently deleted {$count} trashed updates.");

        return Command::SUCCESS;
    }
}
