<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;

class ListCampaigns extends Command
{
    protected $signature = 'campaigns:list';
    protected $description = 'List all campaigns in the database';

    public function handle()
    {
        $campaigns = Campaign::with('charity:id,name')->get();
        
        if ($campaigns->isEmpty()) {
            $this->info('No campaigns found in database.');
            return 0;
        }

        $this->info('Campaigns in database:');
        $this->newLine();
        
        $tableData = [];
        foreach ($campaigns as $campaign) {
            $tableData[] = [
                'ID' => $campaign->id,
                'Title' => $campaign->title,
                'Status' => $campaign->status,
                'Charity' => $campaign->charity->name ?? 'N/A',
                'Created' => $campaign->created_at->format('Y-m-d'),
            ];
        }
        
        $this->table(
            ['ID', 'Title', 'Status', 'Charity', 'Created'],
            $tableData
        );
        
        $this->newLine();
        $this->info('To delete a campaign, use: php artisan campaigns:delete {id}');
        
        return 0;
    }
}
