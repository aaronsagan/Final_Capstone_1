<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class VerificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(){
        return Charity::with('owner')
            ->where('verification_status','pending')
            ->latest()
            ->paginate(20);
    }

    public function getAllCharities(){
        return Charity::with('owner')
            ->latest()
            ->paginate(20);
    }

    public function getUsers(){
        return \App\Models\User::latest()->paginate(20);
    }

    public function activateUser(\Illuminate\Http\Request $r, \App\Models\User $user){
        $user->update(['status'=>'active']);
        return response()->json(['message'=>'User activated']);
    }

    public function approve(Request $r, Charity $charity){
        $charity->update([
            'verification_status'=>'approved',
            'verified_at'=>now(),
            'verification_notes'=>$r->input('notes')
        ]);

        // Send notification to charity owner
        $this->notificationService->sendVerificationStatus($charity, 'approved');

        return response()->json(['message'=>'Approved']);
    }

    public function reject(Request $r, Charity $charity){
        $rejectionReason = $r->input('reason');
        $charity->update([
            'verification_status'=>'rejected',
            'rejection_reason' => $rejectionReason,
            'verification_notes'=>$r->input('notes')
        ]);

        // Send notification to charity owner
        $this->notificationService->sendVerificationStatus($charity, 'rejected');

        return response()->json(['message'=>'Rejected']);
    }

    public function suspendUser(Request $r, \App\Models\User $user){
        $user->update(['status'=>'suspended']);
        return response()->json(['message'=>'User suspended']);
    }
}
