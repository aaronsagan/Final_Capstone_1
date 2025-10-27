<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LocationController extends Controller
{
    /**
     * Get all Philippine locations data
     */
    public function index()
    {
        $jsonPath = database_path('data/ph_locations.json');
        
        if (!File::exists($jsonPath)) {
            return response()->json([
                'error' => 'Location data not found'
            ], 404);
        }
        
        $locations = json_decode(File::get($jsonPath), true);
        
        return response()->json($locations);
    }

    /**
     * Get all regions
     */
    public function getRegions()
    {
        $jsonPath = database_path('data/ph_locations.json');
        
        if (!File::exists($jsonPath)) {
            return response()->json([
                'error' => 'Location data not found'
            ], 404);
        }
        
        $locations = json_decode(File::get($jsonPath), true);
        
        $regions = array_map(function($region) {
            return [
                'code' => $region['code'],
                'name' => $region['name']
            ];
        }, $locations['regions']);
        
        return response()->json(['regions' => $regions]);
    }

    /**
     * Get provinces by region code
     */
    public function getProvinces($regionCode)
    {
        $jsonPath = database_path('data/ph_locations.json');
        
        if (!File::exists($jsonPath)) {
            return response()->json([
                'error' => 'Location data not found'
            ], 404);
        }
        
        $locations = json_decode(File::get($jsonPath), true);
        
        $region = collect($locations['regions'])->firstWhere('code', $regionCode);
        
        if (!$region) {
            return response()->json([
                'error' => 'Region not found'
            ], 404);
        }
        
        $provinces = array_map(function($province) {
            return [
                'code' => $province['code'],
                'name' => $province['name']
            ];
        }, $region['provinces']);
        
        return response()->json(['provinces' => $provinces]);
    }

    /**
     * Get cities by region and province code
     */
    public function getCities($regionCode, $provinceCode)
    {
        $jsonPath = database_path('data/ph_locations.json');
        
        if (!File::exists($jsonPath)) {
            return response()->json([
                'error' => 'Location data not found'
            ], 404);
        }
        
        $locations = json_decode(File::get($jsonPath), true);
        
        $region = collect($locations['regions'])->firstWhere('code', $regionCode);
        
        if (!$region) {
            return response()->json([
                'error' => 'Region not found'
            ], 404);
        }
        
        $province = collect($region['provinces'])->firstWhere('code', $provinceCode);
        
        if (!$province) {
            return response()->json([
                'error' => 'Province not found'
            ], 404);
        }
        
        return response()->json(['cities' => $province['cities']]);
    }
}
