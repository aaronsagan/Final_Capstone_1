# Test Campaign Donations Fix
# Verify that campaign donations are showing correctly

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Testing Campaign Donations Display" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Set-Location capstone_backend

# Test 1: Check donations with campaign_id in database
Write-Host "Test 1: Checking donations with campaigns in database..." -ForegroundColor Yellow
Write-Host ""

php artisan tinker --execute="
\$donations = DB::table('donations')
    ->select(
        'donations.id',
        'donations.campaign_id',
        'donations.amount',
        'donations.status',
        DB::raw('COALESCE(campaigns.title, \"General Donation\") as campaign_name'),
        'charities.name as charity_name'
    )
    ->leftJoin('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
    ->join('charities', 'donations.charity_id', '=', 'charities.id')
    ->orderBy('donations.created_at', 'desc')
    ->limit(10)
    ->get();

foreach (\$donations as \$d) {
    echo \"ID: {\$d->id} | Campaign: {\$d->campaign_name} | Amount: ₱{\$d->amount} | Status: {\$d->status}\n\";
}

\$withCampaign = DB::table('donations')->whereNotNull('campaign_id')->count();
\$generalDonations = DB::table('donations')->whereNull('campaign_id')->count();
echo \"\n\";
echo \"Summary:\n\";
echo \"- Campaign-specific donations: \$withCampaign\n\";
echo \"- General donations: \$generalDonations\n\";
"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Test 2: Checking API Response Format" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To verify the API is returning campaign data:" -ForegroundColor Yellow
Write-Host "1. Login as charity admin in the browser" -ForegroundColor White
Write-Host "2. Open DevTools (F12) > Network tab" -ForegroundColor White
Write-Host "3. Navigate to Donation Management page" -ForegroundColor White
Write-Host "4. Find: GET /api/charities/{id}/donations" -ForegroundColor White
Write-Host "5. Check response includes 'campaign' object" -ForegroundColor White
Write-Host ""
Write-Host "Example of CORRECT response:" -ForegroundColor Green
Write-Host @"
{
  "data": [
    {
      "id": 1,
      "campaign_id": 3,
      "campaign": {
        "id": 3,
        "title": "Education Fund 2024"
      }
    }
  ]
}
"@ -ForegroundColor Gray
Write-Host ""
Write-Host "Example of WRONG response (missing campaign):" -ForegroundColor Red
Write-Host @"
{
  "data": [
    {
      "id": 1,
      "campaign_id": 3,
      "campaign": null
    }
  ]
}
"@ -ForegroundColor Gray

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Test 3: Create Test Donation" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Want to create a test campaign donation? (Y/N): " -ForegroundColor Yellow -NoNewline
$response = Read-Host

if ($response -eq 'Y' -or $response -eq 'y') {
    Write-Host ""
    Write-Host "Creating test donation..." -ForegroundColor Yellow
    
    php artisan tinker --execute="
    \$charity = \App\Models\Charity::first();
    \$campaign = \App\Models\Campaign::first();
    \$donor = \App\Models\User::where('role', 'donor')->first();
    
    if (!\$charity || !\$campaign || !\$donor) {
        echo \"Error: Missing charity, campaign, or donor in database\n\";
        exit;
    }
    
    \$donation = \App\Models\Donation::create([
        'donor_id' => \$donor->id,
        'charity_id' => \$charity->id,
        'campaign_id' => \$campaign->id,
        'amount' => 1000,
        'purpose' => 'general',
        'is_anonymous' => false,
        'status' => 'pending',
        'donated_at' => now(),
    ]);
    
    echo \"✓ Test donation created!\n\";
    echo \"  Donor: {\$donor->name}\n\";
    echo \"  Charity: {\$charity->name}\n\";
    echo \"  Campaign: {\$campaign->title}\n\";
    echo \"  Amount: ₱1,000\n\";
    echo \"\n\";
    echo \"Now check the Donation Management page to see if it displays correctly.\n\";
    "
    
    Write-Host ""
    Write-Host "Test donation created successfully!" -ForegroundColor Green
} else {
    Write-Host "Skipped test donation creation." -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Quick Verification Steps" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Login as charity admin" -ForegroundColor White
Write-Host "2. Go to: Donation Management" -ForegroundColor White
Write-Host "3. Look for donations with campaign names (not 'General Donation')" -ForegroundColor White
Write-Host "4. Verify the campaign column shows actual campaign titles" -ForegroundColor White
Write-Host ""
Write-Host "If you still see 'General Donation' for campaign donations:" -ForegroundColor Yellow
Write-Host "- Clear browser cache and refresh" -ForegroundColor White
Write-Host "- Check that backend server is running the latest code" -ForegroundColor White
Write-Host "- Verify campaign_id is not NULL in database" -ForegroundColor White
Write-Host ""

Set-Location ..
