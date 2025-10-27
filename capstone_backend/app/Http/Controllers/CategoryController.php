<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Get all active categories (Public)
     */
    public function index()
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('activeCampaigns')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get all categories for admin management (Admin only)
     */
    public function adminIndex()
    {
        $categories = Category::withCount('campaigns')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Create a new category (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = $request->user();

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon ?? 'folder',
            'color' => $request->color ?? '#3B82F6',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        // Log admin action
        AdminActionLog::logAction(
            $admin->id,
            'create_category',
            'Category',
            $category->id,
            [
                'name' => $category->name,
                'slug' => $category->slug,
            ]
        );

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    /**
     * Get single category (Public)
     */
    public function show(Category $category)
    {
        $category->loadCount('activeCampaigns');
        return response()->json($category);
    }

    /**
     * Update category (Admin only)
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = $request->user();
        $oldData = $category->toArray();

        $category->update($request->only([
            'name',
            'description',
            'icon',
            'color',
            'sort_order',
            'is_active',
        ]));

        // Log admin action
        AdminActionLog::logAction(
            $admin->id,
            'update_category',
            'Category',
            $category->id,
            [
                'old_data' => $oldData,
                'new_data' => $category->fresh()->toArray(),
            ]
        );

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    /**
     * Delete category (Admin only)
     */
    public function destroy(Category $category)
    {
        $admin = request()->user();

        // Check if category has campaigns
        if ($category->campaigns()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete category with existing campaigns. Please reassign campaigns first.',
            ], 400);
        }

        // Log admin action
        AdminActionLog::logAction(
            $admin->id,
            'delete_category',
            'Category',
            $category->id,
            [
                'name' => $category->name,
                'slug' => $category->slug,
            ]
        );

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get category statistics (Admin only)
     */
    public function statistics()
    {
        return response()->json([
            'total_categories' => Category::count(),
            'active_categories' => Category::where('is_active', true)->count(),
            'categories_with_campaigns' => Category::has('campaigns')->count(),
            'most_used_categories' => Category::withCount('campaigns')
                ->orderBy('campaigns_count', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
