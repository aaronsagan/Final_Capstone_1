<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CharityDocument;
use App\Models\Notification;
use Carbon\Carbon;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'documents:check-expiry';
    protected $description = 'Check for expiring charity documents and send notifications';

    public function handle()
    {
        $this->info('Checking for expiring documents...');

        // Get documents expiring in 30 days
        $expiringDocuments = CharityDocument::where('expires', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays(30)->toDateString()
            ])
            ->whereNull('renewal_reminder_sent_at')
            ->with(['charity.owner'])
            ->get();

        $count = 0;

        foreach ($expiringDocuments as $document) {
            $daysUntilExpiry = Carbon::now()->diffInDays($document->expiry_date, false);
            
            if ($daysUntilExpiry <= 30) {
                // Create notification for charity owner
                Notification::create([
                    'user_id' => $document->charity->owner_id,
                    'title' => 'Document Expiry Alert',
                    'message' => "Your {$document->doc_type} document will expire in {$daysUntilExpiry} days. Please renew it to maintain your charity's verification status.",
                    'type' => 'document_expiry',
                    'data' => json_encode([
                        'document_id' => $document->id,
                        'document_type' => $document->doc_type,
                        'expiry_date' => $document->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                    ]),
                ]);

                // Mark reminder as sent
                $document->update(['renewal_reminder_sent_at' => now()]);
                $count++;

                $this->info("Sent expiry notification for {$document->charity->name} - {$document->doc_type}");
            }
        }

        // Get already expired documents
        $expiredDocuments = CharityDocument::where('expires', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::now()->toDateString())
            ->with(['charity.owner'])
            ->get();

        foreach ($expiredDocuments as $document) {
            // Create urgent notification
            Notification::create([
                'user_id' => $document->charity->owner_id,
                'title' => 'Document Expired - Urgent Action Required',
                'message' => "Your {$document->doc_type} document has expired. Your charity verification may be affected. Please upload a renewed document immediately.",
                'type' => 'document_expired',
                'data' => json_encode([
                    'document_id' => $document->id,
                    'document_type' => $document->doc_type,
                    'expiry_date' => $document->expiry_date,
                    'days_overdue' => Carbon::now()->diffInDays($document->expiry_date),
                ]),
            ]);

            $this->warn("Document expired for {$document->charity->name} - {$document->doc_type}");
        }

        $this->info("Document expiry check completed. Sent {$count} notifications.");
        $this->info("Found " . $expiredDocuments->count() . " expired documents.");

        return 0;
    }
}
