<?php

namespace App\Http\Controllers;

use App\Models\Volunteer;
use App\Models\Charity;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VolunteerController extends Controller
{
    /**
     * Get volunteers for a charity (Charity Admin only)
     */
    public function index(Request $request, Charity $charity)
    {
        $query = Volunteer::where('charity_id', $charity->id)
            ->with('campaign:id,title');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by campaign
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($volunteers);
    }

    /**
     * Create a new volunteer (Charity Admin only)
     */
    public function store(Request $request, Charity $charity)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:field_worker,coordinator,driver,medical_staff,teacher,fundraiser,social_media,photographer,translator,other',
            'skills' => 'nullable|string|max:1000',
            'experience' => 'nullable|string|max:1000',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'availability' => 'nullable|array',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if email already exists for this charity
        $existingVolunteer = Volunteer::where('charity_id', $charity->id)
            ->where('email', $request->email)
            ->first();

        if ($existingVolunteer) {
            return response()->json([
                'error' => 'A volunteer with this email already exists for this charity'
            ], 422);
        }

        $volunteer = Volunteer::create([
            'charity_id' => $charity->id,
            'campaign_id' => $request->campaign_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'skills' => $request->skills,
            'experience' => $request->experience,
            'availability' => $request->availability,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Volunteer added successfully',
            'volunteer' => $volunteer->load('campaign:id,title'),
        ], 201);
    }

    /**
     * Get single volunteer (Charity Admin only)
     */
    public function show(Charity $charity, Volunteer $volunteer)
    {
        // Ensure volunteer belongs to this charity
        if ($volunteer->charity_id !== $charity->id) {
            return response()->json(['error' => 'Volunteer not found'], 404);
        }

        $volunteer->load('campaign:id,title');
        return response()->json($volunteer);
    }

    /**
     * Update volunteer (Charity Admin only)
     */
    public function update(Request $request, Charity $charity, Volunteer $volunteer)
    {
        // Ensure volunteer belongs to this charity
        if ($volunteer->charity_id !== $charity->id) {
            return response()->json(['error' => 'Volunteer not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:field_worker,coordinator,driver,medical_staff,teacher,fundraiser,social_media,photographer,translator,other',
            'skills' => 'nullable|string|max:1000',
            'experience' => 'nullable|string|max:1000',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'status' => 'required|in:active,inactive,on_leave',
            'availability' => 'nullable|array',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if email already exists for this charity (excluding current volunteer)
        $existingVolunteer = Volunteer::where('charity_id', $charity->id)
            ->where('email', $request->email)
            ->where('id', '!=', $volunteer->id)
            ->first();

        if ($existingVolunteer) {
            return response()->json([
                'error' => 'A volunteer with this email already exists for this charity'
            ], 422);
        }

        $volunteer->update($request->only([
            'name',
            'email',
            'phone',
            'address',
            'role',
            'skills',
            'experience',
            'campaign_id',
            'status',
            'availability',
            'emergency_contact_name',
            'emergency_contact_phone',
        ]));

        // Set left_at date if status changed to inactive
        if ($request->status === 'inactive' && !$volunteer->left_at) {
            $volunteer->update(['left_at' => now()->toDateString()]);
        } elseif ($request->status === 'active' && $volunteer->left_at) {
            $volunteer->update(['left_at' => null]);
        }

        return response()->json([
            'message' => 'Volunteer updated successfully',
            'volunteer' => $volunteer->load('campaign:id,title'),
        ]);
    }

    /**
     * Delete volunteer (Charity Admin only)
     */
    public function destroy(Charity $charity, Volunteer $volunteer)
    {
        // Ensure volunteer belongs to this charity
        if ($volunteer->charity_id !== $charity->id) {
            return response()->json(['error' => 'Volunteer not found'], 404);
        }

        $volunteer->delete();

        return response()->json([
            'message' => 'Volunteer removed successfully',
        ]);
    }

    /**
     * Get volunteer statistics for charity (Charity Admin only)
     */
    public function statistics(Charity $charity)
    {
        return response()->json([
            'total_volunteers' => Volunteer::where('charity_id', $charity->id)->count(),
            'active_volunteers' => Volunteer::where('charity_id', $charity->id)->where('status', 'active')->count(),
            'inactive_volunteers' => Volunteer::where('charity_id', $charity->id)->where('status', 'inactive')->count(),
            'on_leave_volunteers' => Volunteer::where('charity_id', $charity->id)->where('status', 'on_leave')->count(),
            'by_role' => Volunteer::where('charity_id', $charity->id)
                ->selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->get(),
            'recent_volunteers' => Volunteer::where('charity_id', $charity->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'role', 'status', 'joined_at']),
        ]);
    }
}
