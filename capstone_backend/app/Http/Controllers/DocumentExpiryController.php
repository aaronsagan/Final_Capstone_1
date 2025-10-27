<?php

namespace App\Http\Controllers;

use App\Models\CharityDocument;
use App\Models\Charity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DocumentExpiryController extends Controller
{
    /**
     * Get expiring documents for admin dashboard
     */
    public function getExpiringDocuments(Request $request)
    {
        $days = $request->get('days', 30); // Default 30 days

        $expiringDocuments = CharityDocument::where('expires', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays($days)->toDateString()
            ])
            ->with(['charity:id,name,contact_email'])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($document) {
                $document->days_until_expiry = Carbon::now()->diffInDays($document->expiry_date, false);
                return $document;
            });

        return response()->json([
            'expiring_documents' => $expiringDocuments,
            'total_count' => $expiringDocuments->count(),
        ]);
    }

    /**
     * Get expired documents
     */
    public function getExpiredDocuments()
    {
        $expiredDocuments = CharityDocument::where('expires', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::now()->toDateString())
            ->with(['charity:id,name,contact_email,verification_status'])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($document) {
                $document->days_overdue = Carbon::now()->diffInDays($document->expiry_date);
                return $document;
            });

        return response()->json([
            'expired_documents' => $expiredDocuments,
            'total_count' => $expiredDocuments->count(),
        ]);
    }

    /**
     * Get document expiry statistics
     */
    public function getExpiryStatistics()
    {
        $now = Carbon::now();

        return response()->json([
            'expiring_in_7_days' => CharityDocument::where('expires', true)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$now->toDateString(), $now->copy()->addDays(7)->toDateString()])
                ->count(),
            
            'expiring_in_30_days' => CharityDocument::where('expires', true)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
                ->count(),
            
            'expired' => CharityDocument::where('expires', true)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', $now->toDateString())
                ->count(),
            
            'charities_with_expired_docs' => Charity::whereHas('documents', function($query) use ($now) {
                $query->where('expires', true)
                      ->whereNotNull('expiry_date')
                      ->where('expiry_date', '<', $now->toDateString());
            })->count(),
        ]);
    }

    /**
     * Get charity's document expiry status (for charity dashboard)
     */
    public function getCharityDocumentStatus(Charity $charity)
    {
        $now = Carbon::now();

        $documents = $charity->documents()
            ->where('expires', true)
            ->whereNotNull('expiry_date')
            ->get()
            ->map(function ($document) use ($now) {
                $expiryDate = Carbon::parse($document->expiry_date);
                $document->days_until_expiry = $now->diffInDays($expiryDate, false);
                $document->is_expired = $expiryDate->isPast();
                $document->is_expiring_soon = $expiryDate->isFuture() && $document->days_until_expiry <= 30;
                return $document;
            });

        return response()->json([
            'documents' => $documents,
            'expired_count' => $documents->where('is_expired', true)->count(),
            'expiring_soon_count' => $documents->where('is_expiring_soon', true)->count(),
            'total_expirable_documents' => $documents->count(),
        ]);
    }

    /**
     * Update document expiry information
     */
    public function updateDocumentExpiry(Request $request, CharityDocument $document)
    {
        $request->validate([
            'expires' => 'required|boolean',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        $document->update([
            'expires' => $request->expires,
            'expiry_date' => $request->expires ? $request->expiry_date : null,
            'renewal_reminder_sent_at' => null, // Reset reminder
        ]);

        return response()->json([
            'message' => 'Document expiry information updated successfully',
            'document' => $document,
        ]);
    }
}
