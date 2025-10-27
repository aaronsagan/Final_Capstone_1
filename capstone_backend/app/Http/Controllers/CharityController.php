<?php

namespace App\Http\Controllers;

use App\Models\{Charity, CharityDocument, DonationChannel, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class CharityController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    // Public directory with advanced search and filtering
    public function index(Request $r){
        $q = Charity::query()->where('verification_status','approved');

        // Search by name or description
        if($term = $r->query('q')) {
            $q->where(function($query) use ($term) {
                $query->where('name','like',"%$term%")
                      ->orWhere('mission','like',"%$term%")
                      ->orWhere('vision','like',"%$term%");
            });
        }

        // Filter by category
        if($category = $r->query('category')) {
            $q->where('category', $category);
        }

        // Filter by region
        if($region = $r->query('region')) {
            $q->where('region', $region);
        }

        // Filter by municipality
        if($municipality = $r->query('municipality')) {
            $q->where('municipality', $municipality);
        }

        // Sort options
        $sortBy = $r->query('sort', 'name'); // name, created_at, total_received
        switch($sortBy) {
            case 'newest':
                $q->orderBy('created_at', 'desc');
                break;
            case 'total_received':
                $q->leftJoin('donations', function($join) {
                    $join->on('charities.id', '=', 'donations.charity_id')
                         ->where('donations.status', '=', 'completed');
                })
                ->selectRaw('charities.*, COALESCE(SUM(donations.amount), 0) as total_received')
                ->groupBy('charities.id')
                ->orderBy('total_received', 'desc');
                break;
            default:
                $q->orderBy('name');
        }

        // Get unique values for filters
        $filters = [
            'categories' => Charity::where('verification_status','approved')
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category'),
            'regions' => Charity::where('verification_status','approved')
                ->whereNotNull('region')
                ->distinct()
                ->pluck('region'),
        ];

        $charities = $q->paginate(12);

        return response()->json([
            'charities' => $charities,
            'filters' => $filters,
            'total' => $charities->total(),
        ]);
    }

    public function show(Charity $charity){
        return $charity->load([
            'documents',
            'owner:id,name,email'
        ])->loadCount(['donations as total_received' => function($q){
            $q->where('status','completed')->select(\DB::raw('coalesce(sum(amount),0)'));
        }]);
    }

    // Charity Admin creates their org
    public function store(Request $r){
        $r->validate(['name'=>'required|string|max:255']);
        $charity = Charity::create([
            'owner_id'=>$r->user()->id,
            'name'=>$r->input('name'),
            'mission'=>$r->input('mission'),
            'contact_email'=>$r->input('contact_email'),
            'contact_phone'=>$r->input('contact_phone'),
        ]);

        // Notify admins about new charity registration
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $this->notificationService->sendSystemAlert(
                $admin,
                "A new charity '{$charity->name}' has registered and needs verification.",
                'info'
            );
        }

        return response()->json($charity,201);
    }

    // Update org
    public function update(Request $r, Charity $charity){
        try {
            abort_unless($charity->owner_id === $r->user()->id, 403, 'You can only update your own charity');

            $validated = $r->validate([
                'name' => 'sometimes|string|max:255',
                'acronym' => 'sometimes|nullable|string|max:50',
                'legal_trading_name' => 'sometimes|nullable|string|max:255',
                'reg_no' => 'sometimes|nullable|string|max:255',
                'tax_id' => 'sometimes|nullable|string|max:255',
                'mission' => 'sometimes|nullable|string|max:1000',
                'vision' => 'sometimes|nullable|string|max:2000',
                'goals' => 'sometimes|nullable|string|max:2000',
                'services' => 'sometimes|nullable|string|max:2000',
                'website' => 'sometimes|nullable|string|max:255',
                'contact_email' => 'sometimes|email|max:255',
                'contact_phone' => 'sometimes|nullable|string|max:20',
                'address' => 'sometimes|nullable|string|max:500',
                'region' => 'sometimes|nullable|string|max:255',
                'municipality' => 'sometimes|nullable|string|max:255',
                'category' => 'sometimes|nullable|string|max:255',
                'logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
                'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Handle logo upload
            if ($r->hasFile('logo')) {
                // Delete old logo if exists
                if ($charity->logo_path) {
                    Storage::disk('public')->delete($charity->logo_path);
                }
                $validated['logo_path'] = $r->file('logo')->store('charity_logos', 'public');
            }

            // Handle cover image upload
            if ($r->hasFile('cover_image')) {
                // Delete old cover image if exists
                if ($charity->cover_image) {
                    Storage::disk('public')->delete($charity->cover_image);
                }
                $validated['cover_image'] = $r->file('cover_image')->store('charity_covers', 'public');
            }

            $charity->update($validated);

            return response()->json([
                'message' => 'Charity updated successfully',
                'charity' => $charity->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Charity update failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'charity_id' => $charity->id,
                'user_id' => $r->user()->id
            ]);

            return response()->json([
                'message' => 'Failed to update charity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Upload verification doc
    public function uploadDocument(Request $r, Charity $charity){
        abort_unless($charity->owner_id === $r->user()->id, 403);
        $data = $r->validate([
            'doc_type'=>'required|in:registration,tax,bylaws,audit,other',
            'file'=>'required|file|mimes:pdf,jpg,jpeg,png'
        ]);
        $path = $r->file('file')->store('charity_docs','public');
        $hash = hash_file('sha256', $r->file('file')->getRealPath());
        $doc = $charity->documents()->create([
            'doc_type'=>$data['doc_type'],
            'file_path'=>$path,
            'sha256'=>$hash,
            'uploaded_by'=>$r->user()->id
        ]);
        return response()->json($doc,201);
    }

    // Public charity documents (for viewing by donors and public)
    public function getDocuments(Charity $charity){
        return $charity->documents()->orderBy('created_at', 'desc')->get();
    }

    public function storeChannel(Request $r, Charity $charity){
        // Check if user owns this charity
        $user = $r->user();
        abort_unless($charity->owner_id === $user->id || ($user->role === 'charity_admin' && $charity->owner_id === $user->id), 403, 'You do not have permission to add channels to this charity');
        
        $data = $r->validate([
            'type'=>'required|in:gcash,paymaya,paypal,bank,other',
            'label'=>'required|string|max:255',
            'details'=>'sometimes|array',
            'qr_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:4096'
        ]);
        
        // Initialize details if not provided
        if (!isset($data['details'])) {
            $data['details'] = [];
        }
        
        // If QR image uploaded, store and merge path into details
        if ($r->hasFile('qr_image')) {
            $qrPath = $r->file('qr_image')->store('donation_qr', 'public');
            $data['details']['qr_image'] = $qrPath; // store relative storage path
        }

        return $charity->channels()->create([
            'type'=>$data['type'],
            'label'=>$data['label'],
            'details'=>$data['details'],
        ]);
    }

    // Donation channels listing with visibility gating
    public function channels(Request $r, Charity $charity)
    {
        // TEMPORARY: Return all channels for debugging
        // TODO: Re-enable authorization after testing
        return $charity->channels()->get();
        
        // Only show channels if:
        // - requester is authenticated donor, or
        // - requester is the charity owner (charity_admin)
        // Otherwise, hide donation channels
        $user = $r->user();

        // Debug logging
        \Log::info('Channels request', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'charity_id' => $charity->id,
            'charity_owner_id' => $charity->owner_id,
            'match' => $user && $charity->owner_id === $user->id
        ]);

        if ($user) {
            // Charity admin/owner can always see ALL their channels (including inactive)
            if ($user->role === 'charity_admin' && $charity->owner_id === $user->id) {
                \Log::info('Access granted: charity_admin match');
                return $charity->channels()->get();
            }
            
            // Legacy check: Charity owner can always see all channels
            if ($charity->owner_id === $user->id) {
                \Log::info('Access granted: owner_id match');
                return $charity->channels()->get();
            }
            
            // Donors can see donation channels of verified charities
            if (method_exists($user, 'isDonor')) {
                // if user model has helper; but ensure role via attribute
            }
            if (property_exists($user, 'role') ? $user->role === 'donor' : ($user->role ?? null) === 'donor') {
                if ($charity->verification_status === 'approved') {
                    return $charity->channels()->where('is_active', true)->get();
                }
            }
        }

        // Unauthenticated or not a donor/owner: do not reveal channels
        return response()->json([
            'message' => 'Donation channels are available to logged-in donors only.'
        ], 403);
    }

    /**
     * Update charity profile (limited fields for Edit Profile form)
     * Only allows editing: mission, vision, description, logo, cover_photo, 
     * location info, and primary contact info
     */
    public function updateProfile(Request $r)
    {
        try {
            $user = $r->user();
            
            // Get the charity owned by this user
            $charity = Charity::where('owner_id', $user->id)->first();
            
            if (!$charity) {
                return response()->json([
                    'message' => 'No charity found for this account'
                ], 404);
            }

            // Validate the request - all fields are optional for partial updates
            $validated = $r->validate([
                'mission' => 'nullable|string|min:30|max:6000',
                'vision' => 'nullable|string|max:6000',
                'description' => 'nullable|string|min:50|max:12000',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'street_address' => 'nullable|string',
                'barangay' => 'nullable|string',
                'city' => 'nullable|string',
                'province' => 'nullable|string',
                'region' => 'nullable|string',
                'full_address' => 'nullable|string',
                'address' => 'nullable|string',
                'first_name' => 'nullable|string|max:50',
                'middle_initial' => 'nullable|string|max:1',
                'last_name' => 'nullable|string|max:50',
                'email' => 'nullable|email|unique:charities,contact_email,' . $charity->id,
                'contact_email' => 'nullable|email|unique:charities,contact_email,' . $charity->id,
                'phone' => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
                'contact_phone' => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
                'website' => 'nullable|string|max:255',
                'operating_hours' => 'nullable|string|max:500',
                'facebook_url' => 'nullable|string|max:255',
                'instagram_url' => 'nullable|string|max:255',
                'twitter_url' => 'nullable|string|max:255',
                'linkedin_url' => 'nullable|string|max:255',
                'youtube_url' => 'nullable|string|max:255'
            ]);

            // Handle logo upload
            if ($r->hasFile('logo')) {
                // Delete old logo if exists
                if ($charity->logo_path) {
                    Storage::disk('public')->delete($charity->logo_path);
                }
                
                // Store new logo in charity-specific folder
                $logoPath = $r->file('logo')->store("charities/{$charity->id}", 'public');
                $validated['logo_path'] = $logoPath;
            }

            // Handle cover photo upload
            if ($r->hasFile('cover_photo')) {
                // Delete old cover photo if exists
                if ($charity->cover_image) {
                    Storage::disk('public')->delete($charity->cover_image);
                }
                
                // Store new cover photo in charity-specific folder
                $coverPath = $r->file('cover_photo')->store("charities/{$charity->id}", 'public');
                $validated['cover_image'] = $coverPath;
            }

            // Map frontend field names to database field names
            if (isset($validated['email'])) {
                $validated['contact_email'] = $validated['email'];
                unset($validated['email']);
            }
            if (isset($validated['phone'])) {
                $validated['contact_phone'] = $validated['phone'];
                unset($validated['phone']);
            }

            // Filter out empty values to only update provided fields
            $dataToUpdate = array_filter($validated, function($value) {
                return $value !== null && $value !== '';
            });

            // Update the charity with only the provided fields
            $charity->update($dataToUpdate);

            return response()->json([
                'message' => 'Charity profile updated successfully',
                'charity' => $charity->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Charity profile update failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $r->user()->id
            ]);

            return response()->json([
                'message' => 'Failed to update charity profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
