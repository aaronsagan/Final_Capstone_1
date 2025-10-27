# Test Top Performers Endpoint
Write-Host "`n=== Testing Top Performers Endpoint ===" -ForegroundColor Cyan

# Get auth token from localStorage (you'll need to manually copy this)
Write-Host "`nTo test, first get your auth token:" -ForegroundColor Yellow
Write-Host "1. Open browser DevTools (F12)" -ForegroundColor White
Write-Host "2. Go to Console tab" -ForegroundColor White
Write-Host "3. Run: localStorage.getItem('token')" -ForegroundColor White
Write-Host "4. Copy the token value (without quotes)" -ForegroundColor White
Write-Host "`nThen run this command:" -ForegroundColor Yellow
Write-Host 'curl http://localhost:8000/api/analytics/campaigns/top-performers -H "Authorization: Bearer YOUR_TOKEN_HERE"' -ForegroundColor Green

Write-Host "`n=== Or test without auth (if endpoint is public) ===" -ForegroundColor Cyan
Write-Host "curl http://localhost:8000/api/analytics/campaigns/top-performers" -ForegroundColor Green

Write-Host "`n=== Quick Database Check ===" -ForegroundColor Cyan
Write-Host "Check if campaigns have current_amount:" -ForegroundColor Yellow

$checkQuery = @"
SELECT 
    id,
    title,
    current_amount,
    target_amount,
    status,
    (SELECT COUNT(*) FROM donations WHERE campaign_id = campaigns.id AND status = 'completed') as donation_count
FROM campaigns 
WHERE status != 'archived'
ORDER BY current_amount DESC 
LIMIT 5;
"@

Write-Host "`nRun this SQL query in your database:" -ForegroundColor White
Write-Host $checkQuery -ForegroundColor Gray

Write-Host "`n=== Test with PHP ===" -ForegroundColor Cyan
$phpTest = @"
<?php
// test-endpoint.php - Place this in capstone_backend/public/
require __DIR__ . '/../vendor/autoload.php';
`$app = require_once __DIR__ . '/../bootstrap/app.php';
`$kernel = `$app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;

echo "Testing Top Performers Query...\n\n";

`$campaigns = Campaign::select(
        'campaigns.id',
        'campaigns.title',
        'campaigns.campaign_type',
        'campaigns.charity_id',
        'campaigns.target_amount',
        'campaigns.current_amount',
        'campaigns.status',
        DB::raw('COALESCE(COUNT(donations.id), 0) as donation_count')
    )
    ->leftJoin('donations', function(`$join) {
        `$join->on('campaigns.id', '=', 'donations.campaign_id')
             ->where('donations.status', '=', 'completed');
    })
    ->where('campaigns.status', '!=', 'archived')
    ->with('charity:id,name')
    ->groupBy(
        'campaigns.id',
        'campaigns.title',
        'campaigns.campaign_type',
        'campaigns.charity_id',
        'campaigns.target_amount',
        'campaigns.current_amount',
        'campaigns.status'
    )
    ->orderBy('campaigns.current_amount', 'desc')
    ->limit(5)
    ->get();

echo "Found " . `$campaigns->count() . " campaigns\n\n";

foreach (`$campaigns as `$campaign) {
    echo "ID: " . `$campaign->id . "\n";
    echo "Title: " . `$campaign->title . "\n";
    echo "Current Amount: " . `$campaign->current_amount . "\n";
    echo "Target Amount: " . `$campaign->target_amount . "\n";
    echo "Donation Count: " . `$campaign->donation_count . "\n";
    echo "---\n";
}
?>
"@

Write-Host "`nCreate test-endpoint.php in capstone_backend/public/:" -ForegroundColor White
Write-Host $phpTest -ForegroundColor Gray
Write-Host "`nThen run: php public/test-endpoint.php" -ForegroundColor Green
