<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/test-charity', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        $charity = $user->charity()->first();
        
        return response()->json([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'has_charity_method' => method_exists($user, 'charity'),
            'charity_exists' => $charity ? true : false,
            'charity_data' => $charity ? [
                'id' => $charity->id,
                'name' => $charity->name,
            ] : null
        ]);
    });
});
