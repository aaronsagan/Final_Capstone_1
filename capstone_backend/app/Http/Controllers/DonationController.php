<?php

namespace App\Http\Controllers;

use App\Models\{Donation, Charity, Campaign};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class DonationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    // Donor creates pledge (one-time or recurring)
    public function store(Request $r){
        $data = $r->validate([
            'charity_id'=>'required|exists:charities,id',
            'campaign_id'=>'nullable|exists:campaigns,id',
            'amount'=>'required|numeric|min:1',
            'purpose'=>'nullable|in:general,project,emergency',
            'is_anonymous'=>'boolean',
            'is_recurring'=>'boolean',
            'recurring_type'=>'nullable|in:weekly,monthly,quarterly,yearly',
            'recurring_end_date'=>'nullable|date|after:today',
            'external_ref'=>'nullable|string|max:64'
        ]);

        $donorId = $data['is_anonymous'] ?? false ? null : $r->user()->id;

        $donation = Donation::create([
            'donor_id' => $donorId,
            'charity_id'=>$data['charity_id'],
            'campaign_id'=>$data['campaign_id'] ?? null,
            'amount'=>$data['amount'],
            'purpose'=>$data['purpose'] ?? 'general',
            'is_anonymous'=>$data['is_anonymous'] ?? false,
            'is_recurring'=>$data['is_recurring'] ?? false,
            'recurring_type'=>$data['recurring_type'] ?? null,
            'recurring_end_date'=>$data['recurring_end_date'] ?? null,
            'next_donation_date' => $this->calculateNextDonationDate($data['recurring_type'] ?? null),
            'status'=>'pending',
            'external_ref'=>$data['external_ref'] ?? null,
            'donated_at' => now(),
        ]);

        // If recurring, create the next donation
        if (($data['is_recurring'] ?? false) && ($data['recurring_type'] ?? null)) {
            $this->scheduleNextRecurringDonation($donation);
        }

        return response()->json($donation->load(['charity', 'campaign']), 201);
    }

    // Donor uploads proof (image/pdf)
    public function uploadProof(Request $r, Donation $donation){
        abort_unless($donation->donor_id === $r->user()->id || $donation->donor_id===null, 403);
        $r->validate(['file'=>'required|file|mimes:jpg,jpeg,png,pdf','proof_type'=>'nullable|string|max:50']);
        $path = $r->file('file')->store('proofs','public');
        $donation->update(['proof_path'=>$path,'proof_type'=>$r->input('proof_type')]);
        return $donation->fresh();
    }

    // Submit manual donation with proof (campaign-specific)
    public function submitManualDonation(Request $r, Campaign $campaign){
        $data = $r->validate([
            'donor_name'=>'required|string|max:255',
            'donor_email'=>'nullable|email|max:255',
            'amount'=>'required|numeric|min:1',
            'channel_used'=>'required|string|max:255',
            'reference_number'=>'required|string|max:255',
            'proof_image'=>'required|image|mimes:jpg,jpeg,png|max:2048',
            'message'=>'nullable|string|max:1000',
            'is_anonymous'=>'boolean',
        ]);

        // Store proof image
        $proofPath = $r->file('proof_image')->store('proofs','public');

        // Get donor ID if authenticated
        $donorId = $r->user() ? $r->user()->id : null;
        if($data['is_anonymous'] ?? false) {
            $donorId = null;
        }

        // Create donation record
        $donation = Donation::create([
            'donor_id' => $donorId,
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'] ?? null,
            'charity_id' => $campaign->charity_id,
            'campaign_id' => $campaign->id,
            'amount' => $data['amount'],
            'channel_used' => $data['channel_used'],
            'reference_number' => $data['reference_number'],
            'proof_path' => $proofPath,
            'proof_type' => 'image',
            'message' => $data['message'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'purpose' => 'project',
            'status' => 'pending',
            'donated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Thank you! Your proof of donation has been submitted for review.',
            'donation' => $donation->load(['charity', 'campaign'])
        ], 201);
    }

    // Submit manual donation directly to charity (not campaign-specific)
    public function submitCharityDonation(Request $r, Charity $charity){
        $data = $r->validate([
            'donor_name'=>'required|string|max:255',
            'donor_email'=>'nullable|email|max:255',
            'amount'=>'required|numeric|min:1',
            'channel_used'=>'required|string|max:255',
            'reference_number'=>'required|string|max:255',
            'proof_image'=>'required|image|mimes:jpg,jpeg,png|max:2048',
            'message'=>'nullable|string|max:1000',
            'is_anonymous'=>'boolean',
        ]);

        // Store proof image
        $proofPath = $r->file('proof_image')->store('proofs','public');

        // Get donor ID if authenticated
        $donorId = $r->user() ? $r->user()->id : null;
        if($data['is_anonymous'] ?? false) {
            $donorId = null;
        }

        // Create donation record for direct charity donation
        $donation = Donation::create([
            'donor_id' => $donorId,
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'] ?? null,
            'charity_id' => $charity->id,
            'campaign_id' => null, // Direct to charity, not a campaign
            'amount' => $data['amount'],
            'channel_used' => $data['channel_used'],
            'reference_number' => $data['reference_number'],
            'proof_path' => $proofPath,
            'proof_type' => 'image',
            'message' => $data['message'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'purpose' => 'general', // Direct charity donations are general
            'status' => 'pending',
            'donated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Thank you! Your donation has been submitted for review.',
            'donation' => $donation->load('charity')
        ], 201);
    }

    // Charity inbox (see donations for their org)
    public function charityInbox(Request $r, Charity $charity){
        abort_unless($charity->owner_id === $r->user()->id, 403);
        return $charity->donations()
            ->with(['donor', 'campaign', 'charity'])
            ->latest()
            ->paginate(20);
    }

    // Charity confirms or rejects donation
    public function confirm(Request $r, Donation $donation){
        abort_unless($donation->charity->owner_id === $r->user()->id, 403);
        $data = $r->validate(['status'=>'required|in:completed,rejected']);
        $donation->update([
            'status'=>$data['status'],
            'receipt_no'=> $data['status']==='completed' ? Str::upper(Str::random(10)) : null
        ]);

        // Send notifications
        if($data['status'] === 'completed') {
            $this->notificationService->sendDonationConfirmation($donation);
        }

        return $donation->fresh();
    }

    // Donor: my donations
    public function myDonations(Request $r){
        return $r->user()->donations()->latest()->paginate(20);
    }

    // Download receipt for a donation
    public function downloadReceipt(Request $r, Donation $donation){
        // Check if user owns this donation or if it's anonymous
        if ($donation->donor_id !== $r->user()->id && !$donation->is_anonymous) {
            abort(403, 'Unauthorized');
        }

        // Check if donation is completed
        if ($donation->status !== 'completed') {
            return response()->json(['message' => 'Receipt not available for pending donations'], 422);
        }

        // Generate PDF receipt
        $pdfContent = $this->generateReceiptPDF($donation);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="donation-receipt-' . $donation->receipt_no . '.pdf"'
        ]);
    }

    private function generateReceiptPDF($donation){
        // Simple PDF generation using basic HTML to PDF conversion
        // In a production system, you'd use a proper PDF library like TCPDF or DomPDF

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Donation Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .receipt-info { margin: 20px 0; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Donation Receipt</h1>
                <p>Thank you for your generous donation!</p>
            </div>

            <div class='receipt-info'>
                <p><strong>Receipt Number:</strong> {$donation->receipt_no}</p>
                <p><strong>Date:</strong> {$donation->donated_at->format('F j, Y')}</p>
                <p><strong>Charity:</strong> {$donation->charity->name}</p>
                <p><strong>Amount:</strong> ₱" . number_format($donation->amount, 2) . "</p>
                <p><strong>Purpose:</strong> " . ucfirst($donation->purpose) . "</p>
                " . ($donation->campaign ? "<p><strong>Campaign:</strong> {$donation->campaign->title}</p>" : "") . "
            </div>

            <div class='footer'>
                <p>This receipt is generated electronically and is valid without signature.</p>
                <p>Generated on: " . now()->format('F j, Y \a\t g:i A') . "</p>
            </div>
        </body>
        </html>
        ";

        // For now, return HTML - in production, convert to PDF
        // You would use a library like DomPDF here:
        // return PDF::loadHTML($html)->download('receipt.pdf');

        return $html;
    }

    // Get recurring donations that need processing
    public function processRecurringDonations(){
        $pendingRecurring = Donation::where('is_recurring', true)
            ->where('next_donation_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('recurring_end_date')
                  ->orWhere('recurring_end_date', '>', now());
            })
            ->get();

        foreach($pendingRecurring as $donation){
            $this->processRecurringDonation($donation);
        }

        return response()->json(['processed' => $pendingRecurring->count()]);
    }

    private function calculateNextDonationDate($recurringType){
        if(!$recurringType) return null;

        $now = now();
        switch($recurringType){
            case 'weekly': return $now->addWeek();
            case 'monthly': return $now->addMonth();
            case 'quarterly': return $now->addMonths(3);
            case 'yearly': return $now->addYear();
            default: return null;
        }
    }

    private function scheduleNextRecurringDonation($donation){
        if(!$donation->is_recurring || !$donation->recurring_type) return;

        $nextDate = $this->calculateNextDonationDate($donation->recurring_type);
        if($nextDate && (!$donation->recurring_end_date || $nextDate <= $donation->recurring_end_date)){
            Donation::create([
                'donor_id' => $donation->donor_id,
                'charity_id' => $donation->charity_id,
                'campaign_id' => $donation->campaign_id,
                'amount' => $donation->amount,
                'purpose' => $donation->purpose,
                'is_anonymous' => $donation->is_anonymous,
                'is_recurring' => true,
                'recurring_type' => $donation->recurring_type,
                'recurring_end_date' => $donation->recurring_end_date,
                'next_donation_date' => $nextDate,
                'status' => 'scheduled',
                'parent_donation_id' => $donation->id,
            ]);
        }
    }

    private function processRecurringDonation($donation){
        // Mark as completed and create next one
        $donation->update(['status' => 'completed']);
        $this->scheduleNextRecurringDonation($donation);
    }

    /**
     * Update donation status with optional rejection reason
     */
    public function updateStatus(Request $request, Donation $donation)
    {
        // Check authorization
        abort_unless($donation->charity->owner_id === $request->user()->id, 403, 'Unauthorized');

        $data = $request->validate([
            'status' => 'required|in:pending,completed,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        // Update donation status
        $updateData = ['status' => $data['status']];

        // Generate receipt number if completed
        if ($data['status'] === 'completed') {
            $updateData['receipt_no'] = Str::upper(Str::random(10));
        }

        // Store rejection reason if rejected
        if ($data['status'] === 'rejected' && isset($data['reason'])) {
            $updateData['rejection_reason'] = $data['reason'];
        }

        $donation->update($updateData);

        // Send notifications
        if ($data['status'] === 'completed') {
            $this->notificationService->sendDonationConfirmation($donation);
        }

        return response()->json($donation->fresh()->load(['donor', 'charity', 'campaign']));
    }
}
