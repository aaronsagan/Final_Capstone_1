<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DonorProfile;
use App\Models\DonorRegistrationDraft;
use App\Models\DonorVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DonorRegistrationController extends Controller
{
    public function saveDraft(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'first_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
        ]);

        $draft = DonorRegistrationDraft::firstOrNew(['email' => $data['email']]);
        if (!$draft->token) {
            $draft->token = Str::uuid()->toString();
        }
        $existing = $draft->data ?? [];
        $draft->data = array_merge($existing, $data);
        $draft->save();

        return response()->json(['success' => true, 'token' => $draft->token]);
    }

    public function uploadVerification(Request $r)
    {
        $validated = $r->validate([
            'email' => 'nullable|email',
            'id_type' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:100',
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'selfie_with_id' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $idPath = $r->file('id_document')->store('donor_verifications', 'public');
        $selfiePath = null;
        if ($r->hasFile('selfie_with_id')) {
            $selfiePath = $r->file('selfie_with_id')->store('donor_verifications', 'public');
        }

        $verification = DonorVerification::create([
            'user_id' => auth()->id(),
            'email' => $validated['email'] ?? (auth()->user()?->email) ?? null,
            'id_type' => $validated['id_type'],
            'id_number' => $validated['id_number'] ?? null,
            'id_document_path' => $idPath,
            'selfie_path' => $selfiePath,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'verification' => $verification]);
    }

    public function submit(Request $r)
    {
        $validated = $r->validate([
            // Personal
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            // Address
            'street_address' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'full_address' => 'nullable|string|max:500',
            // Preferences
            'cause_preferences' => 'nullable|array',
            'cause_preferences.*' => 'string',
            'pref_email' => 'required|in:true,false,1,0',
            'pref_sms' => 'required|in:true,false,1,0',
            'pref_updates' => 'required|in:true,false,1,0',
            'pref_urgent' => 'required|in:true,false,1,0',
            'pref_reports' => 'required|in:true,false,1,0',
            // Security
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            $profileImagePath = null;
            if ($r->hasFile('profile_image')) {
                $profileImagePath = $r->file('profile_image')->store('profile_images', 'public');
            }

            $user = User::create([
                'name' => $fullName,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['full_address'] ?? ($validated['street_address'] . ', ' . ($validated['barangay'] ?? '') . ', ' . $validated['city'] . ', ' . $validated['province'] . ', ' . $validated['region']),
                'profile_image' => $profileImagePath,
                'role' => 'donor',
                'status' => 'active',
            ]);

            DonorProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'street_address' => $validated['street_address'],
                'barangay' => $validated['barangay'] ?? null,
                'city' => $validated['city'],
                'province' => $validated['province'],
                'region' => $validated['region'],
                'postal_code' => $validated['postal_code'] ?? null,
                'country' => $validated['country'] ?? 'Philippines',
                'full_address' => $validated['full_address'] ?? null,
                'cause_preferences' => $validated['cause_preferences'] ?? [],
                'pref_email' => filter_var($validated['pref_email'], FILTER_VALIDATE_BOOLEAN),
                'pref_sms' => filter_var($validated['pref_sms'], FILTER_VALIDATE_BOOLEAN),
                'pref_updates' => filter_var($validated['pref_updates'], FILTER_VALIDATE_BOOLEAN),
                'pref_urgent' => filter_var($validated['pref_urgent'], FILTER_VALIDATE_BOOLEAN),
                'pref_reports' => filter_var($validated['pref_reports'], FILTER_VALIDATE_BOOLEAN),
            ]);

            // Link any pending verification to this user
            DonorVerification::whereNull('user_id')
                ->where('email', $user->email)
                ->latest()
                ->limit(1)
                ->update(['user_id' => $user->id]);

            // Cleanup draft
            DonorRegistrationDraft::where('email', $user->email)->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Registration submitted. Your verification is pending review.',
                'user' => $user->load('donorProfile')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Donor submit registration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server error submitting registration'], 500);
        }
    }
}
