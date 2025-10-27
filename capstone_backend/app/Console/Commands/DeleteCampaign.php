<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;

class DeleteCampaign extends Command
{
    protected $signature = 'campaigns:delete {id}';
    protected $description = 'Delete a campaign by ID';

    public function handle()
    {
        $id = $this->argument('id');
        $campaign = Campaign::find($id);
        
        if (!$campaign) {
            $this->error("Campaign with ID {$id} not found.");
            return 1;
        }
        
        $this->info("Campaign found:");
        $this->info("  ID: {$campaign->id}");
        $this->info("  Title: {$campaign->title}");
        $this->info("  Status: {$campaign->status}");
        $this->newLine();
        
        if ($this->confirm('Are you sure you want to delete this campaign?')) {
            $campaign->delete();
            $this->info("Campaign '{$campaign->title}' has been deleted successfully.");
            return 0;
        }
        
        $this->info('Deletion cancelled.');
        return 0;
    }
}
