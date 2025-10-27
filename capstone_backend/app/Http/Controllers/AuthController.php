<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Charity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Services\SecurityService;

class AuthController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }
    public function registerDonor(Request $r){
        try {
            $data = $r->validate([
                'name'=>'required|string|max:255',
                'email'=>'required|email|unique:users,email',
                'password'=>'required|min:6|confirmed',
                'phone'=>'nullable|string',
                'address'=>'nullable|string',
                'profile_image'=>'nullable|image|max:2048',
                // Required location fields
                'region'=>'required|string|max:255',
                'province'=>'required|string|max:255',
                'city'=>'required|string|max:255',
                'barangay'=>'required|string|max:255'
            ]);
            
            // Handle profile image upload
            $profileImagePath = null;
            if ($r->hasFile('profile_image')) {
                $profileImagePath = $r->file('profile_image')->store('profile_images', 'public');
            }
            
            $user = User::create([
                'name'=>$data['name'],
                'email'=>$data['email'],
                'password'=>Hash::make($data['password']),
                'phone'=>$data['phone'] ?? null,
                'address'=>$data['address'] ?? null,
                'profile_image'=>$profileImagePath,
                'region'=>$data['region'],
                'province'=>$data['province'],
                'city'=>$data['city'],
                'barangay'=>$data['barangay'],
                'role'=>'donor',
                'status'=>'active'
            ]);
            
            // Log successful registration
            $this->securityService->logAuthEvent('user_registered', $user, [
                'role' => 'donor',
                'registration_method' => 'email'
            ]);
            
            return response()->json([
                'message' => 'Registration successful',
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Register donor failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Server error creating account'], 500);
        }
    }

    public function registerCharityAdmin(Request $r){
        DB::beginTransaction();
        try {
            // Validate all fields - frontend sends different field names
            $validated = $r->validate([
                // Primary Contact - New structured fields
                'primary_first_name'=>'required|string|max:50',
                'primary_middle_initial'=>'nullable|string|max:1',
                'primary_last_name'=>'required|string|max:50',
                'primary_position'=>'nullable|string|max:100',
                'primary_email'=>'required|email|unique:users,email',
                'primary_phone'=>'required|string|max:15',
                'password'=>'required|string|min:6|confirmed',
                
                // Organization details
                'organization_name'=>'required|string|max:255',
                'registration_number'=>'nullable|string|max:255',
                'tax_id'=>'nullable|string|max:255',
                'mission_statement'=>'nullable|string|max:6000',
                'vision_statement'=>'nullable|string|max:6000',
                'description'=>'nullable|string|max:12000',
                'website'=>'nullable|string',
                
                // Location fields
                'street_address'=>'nullable|string',
                'barangay'=>'required|string|max:255',
                'city'=>'required|string|max:255',
                'province'=>'required|string|max:255',
                'region'=>'required|string|max:255',
                'full_address'=>'nullable|string',
                
                'nonprofit_category'=>'nullable|string',
                'legal_trading_name'=>'nullable|string',
                'accept_terms'=>'nullable',
                'confirm_truthfulness'=>'nullable',
                
                // Files (optional)
                'logo'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'cover_image'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'documents'=>'nullable',
                'doc_types'=>'nullable'
            ]);
            
            // Log file upload attempts for debugging
            Log::info('Charity registration file uploads', [
                'has_logo' => $r->hasFile('logo'),
                'has_cover' => $r->hasFile('cover_image'),
                'logo_size' => $r->hasFile('logo') ? $r->file('logo')->getSize() : 0,
                'cover_size' => $r->hasFile('cover_image') ? $r->file('cover_image')->getSize() : 0,
            ]);
            
            // Create user account with provided password
            // Combine first, middle initial, and last name
            $fullName = trim(implode(' ', array_filter([
                $validated['primary_first_name'],
                $validated['primary_middle_initial'] ?? null,
                $validated['primary_last_name']
            ])));
            
            $user = User::create([
                'name'=>$fullName,
                'email'=>$validated['primary_email'],
                'password'=>Hash::make($validated['password']),
                'phone'=>$validated['primary_phone'],
                'role'=>'charity_admin',
                'status'=>'active'
            ]);
            
            // Handle logo upload
            $logoPath = null;
            if ($r->hasFile('logo')) {
                $logoPath = $r->file('logo')->store('charity_logos', 'public');
                Log::info('Logo uploaded successfully', ['path' => $logoPath]);
            }
            
            // Handle cover image upload
            $coverPath = null;
            if ($r->hasFile('cover_image')) {
                $coverPath = $r->file('cover_image')->store('charity_covers', 'public');
                Log::info('Cover image uploaded successfully', ['path' => $coverPath]);
            }
            
            // Create charity organization with all fields
            $charity = Charity::create([
                'owner_id'=>$user->id,
                'name'=>$validated['organization_name'],
                'legal_trading_name'=>$validated['legal_trading_name'] ?? null,
                'reg_no'=>$validated['registration_number'] ?? null,
                'tax_id'=>$validated['tax_id'] ?? null,
                'mission'=>$validated['mission_statement'] ?? null,
                'vision'=>$validated['vision_statement'] ?? null,
                'description'=>$validated['description'] ?? null,
                'website'=>$validated['website'] ?? null,
                
                // Primary contact fields
                'primary_first_name'=>$validated['primary_first_name'],
                'primary_middle_initial'=>$validated['primary_middle_initial'] ?? null,
                'primary_last_name'=>$validated['primary_last_name'],
                'primary_position'=>$validated['primary_position'] ?? null,
                'primary_email'=>$validated['primary_email'],
                'primary_phone'=>$validated['primary_phone'],
                
                // Location fields
                'street_address'=>$validated['street_address'] ?? null,
                'barangay'=>$validated['barangay'] ?? null,
                'city'=>$validated['city'] ?? null,
                'province'=>$validated['province'] ?? null,
                'region'=>$validated['region'] ?? null,
                'full_address'=>$validated['full_address'] ?? null,
                
                'category'=>$validated['nonprofit_category'] ?? null,
                'logo_path'=>$logoPath,
                'cover_image'=>$coverPath,
                'verification_status'=>'pending'
            ]);
            
            // Handle document uploads
            if ($r->hasFile('documents')) {
                // Map frontend doc types to database enum values
                $docTypeMap = [
                    'registration_cert' => 'registration',
                    'tax_registration' => 'tax',
                    'financial_statement' => 'audit',
                    'representative_id' => 'other',
                    'additional_docs' => 'other'
                ];
                
                foreach ($r->file('documents') as $index => $file) {
                    $frontendDocType = $r->input("doc_types.{$index}", 'other');
                    $docType = $docTypeMap[$frontendDocType] ?? 'other';
                    $path = $file->store('charity_docs', 'public');
                    $hash = hash_file('sha256', $file->getRealPath());
                    
                    $charity->documents()->create([
                        'doc_type'=>$docType,
                        'file_path'=>$path,
                        'sha256'=>$hash,
                        'uploaded_by'=>$user->id
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Registration successful. Your charity is pending verification.',
                'user' => $user,
                'charity' => $charity->load('documents')
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Charity registration validation failed', [
                'errors' => $e->errors(),
                'input' => $r->except(['password', 'password_confirmation'])
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register charity admin failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $r->except(['password', 'password_confirmation'])
            ]);
            return response()->json([
                'message' => 'Server error creating account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $r){
        $data = $r->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);
        $user = User::where('email',$data['email'])->first();
        if(!$user || !Hash::check($data['password'], $user->password) || $user->status!=='active'){
            // Log failed login attempt
            $this->securityService->logFailedLogin($data['email'], $r->ip(), 'invalid_credentials');
            return response()->json(['message'=>'Invalid credentials'], 401);
        }

        // Log successful login
        $this->securityService->logAuthEvent('user_login', $user, [
            'login_method' => 'password'
        ]);

        // Check for suspicious login patterns
        $this->securityService->checkSuspiciousLogin($user, $r->ip());

        $token = $user->createToken('auth')->plainTextToken;

        // Load charity data for charity admins
        $responseUser = $user;
        if ($user->role === 'charity_admin' && $user->charity) {
            $responseUser = $user->load('charity');
        }

        return response()->json(['token' => $token, 'user' => $responseUser]);
    }

    public function logout(Request $r){
        $r->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }

    public function me(Request $r){
        $user = $r->user();
        
        // Load charity data for charity admins
        if ($user->role === 'charity_admin' && $user->charity) {
            $user = $user->load('charity');
        }
        
        return response()->json($user);
    }

    public function updateProfile(Request $r){
        $user = $r->user();

        // Enhanced validation based on user role
        $validationRules = [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
        ];

        // Add role-specific fields
        if ($user->role === 'donor') {
            $validationRules['profile_image'] = 'sometimes|image|mimes:jpeg,png,jpg|max:2048';
        }

        if ($user->role === 'charity_admin') {
            $validationRules['contact_person_name'] = 'sometimes|string|max:255';
            $validationRules['contact_email'] = 'sometimes|email|unique:users,email,' . $user->id;
            $validationRules['contact_phone'] = 'sometimes|nullable|string|max:20';
            $validationRules['organization_name'] = 'sometimes|string|max:255';
            $validationRules['registration_number'] = 'sometimes|nullable|string|max:255';
            $validationRules['tax_id'] = 'sometimes|nullable|string|max:255';
            $validationRules['mission_statement'] = 'sometimes|nullable|string|max:1000';
            $validationRules['vision_statement'] = 'sometimes|nullable|string|max:1000';
            $validationRules['description'] = 'sometimes|nullable|string|max:1000';
            $validationRules['website'] = 'sometimes|nullable|string|max:255';
            $validationRules['address'] = 'sometimes|nullable|string|max:500';
            $validationRules['region'] = 'sometimes|nullable|string|max:255';
            $validationRules['municipality'] = 'sometimes|nullable|string|max:255';
            $validationRules['nonprofit_category'] = 'sometimes|nullable|string|max:255';
            $validationRules['legal_trading_name'] = 'sometimes|nullable|string|max:255';
            $validationRules['logo'] = 'sometimes|image|mimes:jpeg,png,jpg|max:2048';
            $validationRules['cover_image'] = 'sometimes|image|mimes:jpeg,png,jpg|max:2048';
        }

        $validatedData = $r->validate($validationRules);

        // Handle profile image upload for donors
        if ($r->hasFile('profile_image') && $user->role === 'donor') {
            // Delete old profile image if exists
            if ($user->profile_image) {
                \Storage::disk('public')->delete($user->profile_image);
            }
            $validatedData['profile_image'] = $r->file('profile_image')->store('profile_images', 'public');
        }

        // Handle charity logo upload for charity admins
        if ($r->hasFile('logo') && $user->role === 'charity_admin') {
            // Delete old logo if exists
            if ($user->charity && $user->charity->logo_path) {
                \Storage::disk('public')->delete($user->charity->logo_path);
            }
            $validatedData['logo_path'] = $r->file('logo')->store('charity_logos', 'public');
        }

        // Handle charity cover image upload for charity admins
        if ($r->hasFile('cover_image') && $user->role === 'charity_admin') {
            // Delete old cover image if exists
            if ($user->charity && $user->charity->cover_image) {
                \Storage::disk('public')->delete($user->charity->cover_image);
            }
            $validatedData['cover_image'] = $r->file('cover_image')->store('charity_covers', 'public');
        }

        // Update user profile
        $user->update($validatedData);

        // Update charity information if user is charity admin
        if ($user->role === 'charity_admin' && $user->charity) {
            $charityData = [];

            // Map form fields to charity model fields
            if (isset($validatedData['contact_person_name'])) {
                $charityData['contact_email'] = $validatedData['contact_email'] ?? $user->email;
                $charityData['contact_phone'] = $validatedData['contact_phone'] ?? null;
            }

            if (isset($validatedData['organization_name'])) {
                $charityData['name'] = $validatedData['organization_name'];
            }

            if (isset($validatedData['registration_number'])) {
                $charityData['reg_no'] = $validatedData['registration_number'];
            }

            if (isset($validatedData['tax_id'])) {
                $charityData['tax_id'] = $validatedData['tax_id'];
            }

            if (isset($validatedData['mission_statement'])) {
                $charityData['mission'] = $validatedData['mission_statement'];
            }

            if (isset($validatedData['description'])) {
                $charityData['vision'] = $validatedData['description'];
            }

            if (isset($validatedData['website'])) {
                $charityData['website'] = $validatedData['website'];
            }

            if (isset($validatedData['address'])) {
                $charityData['address'] = $validatedData['address'];
            }

            if (isset($validatedData['region'])) {
                $charityData['region'] = $validatedData['region'];
            }

            if (isset($validatedData['municipality'])) {
                $charityData['municipality'] = $validatedData['municipality'];
            }

            if (isset($validatedData['nonprofit_category'])) {
                $charityData['category'] = $validatedData['nonprofit_category'];
            }

            if (isset($validatedData['legal_trading_name'])) {
                $charityData['legal_trading_name'] = $validatedData['legal_trading_name'];
            }

            if (isset($validatedData['logo_path'])) {
                $charityData['logo_path'] = $validatedData['logo_path'];
            }

            if (isset($validatedData['cover_image'])) {
                $charityData['cover_image'] = $validatedData['cover_image'];
            }

            // Only update if there are charity fields to update
            if (!empty($charityData)) {
                $user->charity->update($charityData);
            }
        }

        // Log the profile update
        $this->securityService->logActivity($user, 'profile_updated', [
            'updated_fields' => array_keys($validatedData),
            'user_role' => $user->role
        ]);

        // Return updated user with charity data if applicable
        $responseData = $user->fresh(); // Get fresh data from database
        if ($user->charity) {
            $responseData = $user->load('charity');
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $responseData
        ]);
    }

    public function changePassword(Request $r){
        $user = $r->user();

        $data = $r->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['new_password'])
        ]);

        // Log password change
        $this->securityService->logActivity($user, 'password_changed', [
            'changed_at' => now()->toISOString()
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function deactivateAccount(Request $r){
        $user = $r->user();

        // Log account deactivation
        $this->securityService->logActivity($user, 'account_deactivated', [
            'deactivated_at' => now()->toISOString(),
            'reason' => $r->input('reason', 'User requested deactivation')
        ]);

        // Set status to inactive (soft delete)
        $user->update(['status' => 'inactive']);

        return response()->json(['message' => 'Account deactivated successfully']);
    }

    public function deleteAccount(Request $r){
        $user = $r->user();

        $data = $r->validate([
            'password' => 'required|string',
            'reason' => 'nullable|string|max:500'
        ]);

        // Verify password before deletion
        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Password is incorrect'], 422);
        }

        // Log account deletion
        $this->securityService->logActivity($user, 'account_deleted', [
            'deleted_at' => now()->toISOString(),
            'reason' => $data['reason'] ?? 'User requested deletion'
        ]);

        // Hard delete the user and all associated data
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
