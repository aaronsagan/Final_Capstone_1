# Test Campaign Progress Fix
# Verify that campaign progress is calculating correctly from donations

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Testing Campaign Progress Calculation" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Set-Location capstone_backend

# Test 1: Check campaign progress in database
Write-Host "Test 1: Calculating campaign progress from donations..." -ForegroundColor Yellow
Write-Host ""

php artisan tinker --execute="
\$campaigns = DB::table('campaigns')
    ->select(
        'campaigns.id',
        'campaigns.title',
        'campaigns.target_amount',
        'campaigns.status',
        DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM donations WHERE campaign_id = campaigns.id AND status = \"completed\") as current_amount'),
        DB::raw('(SELECT COUNT(*) FROM donations WHERE campaign_id = campaigns.id AND status = \"completed\") as donation_count')
    )
    ->where('campaigns.status', '!=', 'draft')
    ->get();

echo \"Campaign Progress Report\n\";
echo \"========================\n\n\";

foreach (\$campaigns as \$c) {
    \$progress = \$c->target_amount > 0 ? round((\$c->current_amount / \$c->target_amount) * 100, 1) : 0;
    \$progressBar = str_repeat('█', (int)(\$progress / 5)) . str_repeat('░', 20 - (int)(\$progress / 5));
    
    echo \"Campaign: {\$c->title}\n\";
    echo \"  Target: ₱\" . number_format(\$c->target_amount, 2) . \"\n\";
    echo \"  Current: ₱\" . number_format(\$c->current_amount, 2) . \"\n\";
    echo \"  Progress: {\$progress}% [\$progressBar]\n\";
    echo \"  Donations: {\$c->donation_count} completed\n\";
    echo \"  Status: {\$c->status}\n\";
    echo \"\n\";
}

\$totalCampaigns = count(\$campaigns);
\$withDonations = 0;
\$totalRaised = 0;

foreach (\$campaigns as \$c) {
    if (\$c->current_amount > 0) \$withDonations++;
    \$totalRaised += \$c->current_amount;
}

echo \"Summary:\n\";
echo \"--------\n\";
echo \"Total Campaigns: \$totalCampaigns\n\";
echo \"Campaigns with donations: \$withDonations\n\";
echo \"Total raised: ₱\" . number_format(\$totalRaised, 2) . \"\n\";
"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Test 2: Check Campaign Model Accessor" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

php artisan tinker --execute="
\$campaign = App\Models\Campaign::first();

if (!\$campaign) {
    echo \"No campaigns found. Create a campaign first.\n\";
    exit;
}

echo \"Testing Campaign Model:\n\";
echo \"  ID: {\$campaign->id}\n\";
echo \"  Title: {\$campaign->title}\n\";
echo \"  Target: ₱\" . number_format(\$campaign->target_amount ?? 0, 2) . \"\n\";
echo \"  Current Amount (accessor): ₱\" . number_format(\$campaign->current_amount, 2) . \"\n\";
echo \"\n\";

// Check if current_amount is in JSON
\$json = \$campaign->toArray();
if (isset(\$json['current_amount'])) {
    echo \"✓ current_amount is included in JSON response\n\";
} else {
    echo \"✗ current_amount NOT in JSON (check \\\$appends)\n\";
}
"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Test 3: API Endpoint Test" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To test the API response:" -ForegroundColor Yellow
Write-Host "1. Make sure backend is running: php artisan serve" -ForegroundColor White
Write-Host "2. Open browser DevTools (F12)" -ForegroundColor White
Write-Host "3. Navigate to Campaign Management page" -ForegroundColor White
Write-Host "4. Network tab → Find: GET /api/charities/{id}/campaigns" -ForegroundColor White
Write-Host "5. Check response includes 'current_amount' field" -ForegroundColor White
Write-Host ""
Write-Host "Expected response format:" -ForegroundColor Green
Write-Host @"
{
  "data": [
    {
      "id": 1,
      "title": "Education Fund",
      "target_amount": "10000.00",
      "current_amount": 3500.00,    ← Should be present
      "status": "published"
    }
  ]
}
"@ -ForegroundColor Gray

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Test 4: Create Test Scenario (Optional)" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Want to create a test campaign with donations? (Y/N): " -ForegroundColor Yellow -NoNewline
$response = Read-Host

if ($response -eq 'Y' -or $response -eq 'y') {
    Write-Host ""
    Write-Host "Creating test scenario..." -ForegroundColor Yellow
    
    php artisan tinker --execute="
    \$charity = App\Models\Charity::first();
    \$donor = App\Models\User::where('role', 'donor')->first();
    
    if (!\$charity) {
        echo \"Error: No charity found\n\";
        exit;
    }
    
    if (!\$donor) {
        echo \"Error: No donor found\n\";
        exit;
    }
    
    // Create a test campaign
    \$campaign = App\Models\Campaign::create([
        'charity_id' => \$charity->id,
        'title' => 'Test Campaign - ' . now()->format('Y-m-d H:i:s'),
        'description' => 'A test campaign to verify progress calculation',
        'target_amount' => 10000,
        'status' => 'published',
        'donation_type' => 'one_time',
        'start_date' => now(),
        'end_date' => now()->addDays(30),
    ]);
    
    echo \"✓ Created campaign: {\$campaign->title}\n\";
    echo \"  ID: {\$campaign->id}\n\";
    echo \"  Target: ₱10,000\n\";
    echo \"\n\";
    
    // Create test donations
    \$donations = [
        ['amount' => 1000, 'status' => 'completed'],
        ['amount' => 2500, 'status' => 'completed'],
        ['amount' => 1500, 'status' => 'completed'],
        ['amount' => 500, 'status' => 'pending'],  // Should not count
    ];
    
    foreach (\$donations as \$d) {
        App\Models\Donation::create([
            'donor_id' => \$donor->id,
            'charity_id' => \$charity->id,
            'campaign_id' => \$campaign->id,
            'amount' => \$d['amount'],
            'status' => \$d['status'],
            'purpose' => 'general',
            'is_anonymous' => false,
            'donated_at' => now(),
        ]);
        
        \$status = \$d['status'] == 'completed' ? '✓' : '○';
        echo \"  \$status Donation: ₱\" . number_format(\$d['amount']) . \" ({\$d['status']})\n\";
    }
    
    echo \"\n\";
    
    // Refresh and check current_amount
    \$campaign = \$campaign->fresh();
    \$expected = 1000 + 2500 + 1500; // Only completed
    \$actual = \$campaign->current_amount;
    
    echo \"Expected current_amount: ₱\" . number_format(\$expected) . \"\n\";
    echo \"Actual current_amount: ₱\" . number_format(\$actual) . \"\n\";
    echo \"Progress: \" . round((\$actual / 10000) * 100, 1) . \"%\n\";
    echo \"\n\";
    
    if (\$actual == \$expected) {
        echo \"✓ TEST PASSED - Progress calculation working correctly!\n\";
    } else {
        echo \"✗ TEST FAILED - Expected \$expected but got \$actual\n\";
    }
    "
    
    Write-Host ""
    Write-Host "Test scenario created!" -ForegroundColor Green
    Write-Host "Check the Campaign Management page to see the progress." -ForegroundColor White
} else {
    Write-Host "Skipped test scenario creation." -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Verification Steps" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To verify the fix is working:" -ForegroundColor Yellow
Write-Host "1. Login as charity admin" -ForegroundColor White
Write-Host "2. Navigate to: Campaign Management (/charity/campaigns)" -ForegroundColor White
Write-Host "3. Check that progress bars show actual percentages" -ForegroundColor White
Write-Host "4. Verify current amounts match donation totals" -ForegroundColor White
Write-Host "5. Confirm a pending donation and see progress update" -ForegroundColor White
Write-Host ""
Write-Host "Common Issues:" -ForegroundColor Yellow
Write-Host "- If still showing 0: Clear browser cache and hard refresh (Ctrl+Shift+R)" -ForegroundColor White
Write-Host "- If 'current_amount' not in API: Check Campaign model has \$appends" -ForegroundColor White
Write-Host "- If wrong calculation: Ensure only 'completed' donations count" -ForegroundColor White
Write-Host ""

Set-Location ..
