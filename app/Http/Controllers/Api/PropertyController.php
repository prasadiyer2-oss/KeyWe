<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class PropertyController extends Controller
{
    protected $propertyService;

    // Dependency Injection: Laravel automatically gives us the Service
    public function __construct(PropertyService $propertyService, LocationService $locationService)
    {
        $this->propertyService = $propertyService;
        $this->locationService = $locationService;
    }

    /**
     * GET /api/v1/properties
     */
    public function index(Request $request)
    {
        try {
            // 1. Detect Location (only if user hasn't typed a search)
            $detectedCity = null;
            
            if (!$request->has('search') && !$request->has('location')) {
                $detectedCity = $this->locationService->detectCity($request);
            }

            // 2. Fetch Properties (Pass the city)
            $properties = $this->propertyService->listProperties($request, $detectedCity);

            return response()->json([
                'status' => 'success',
                'message' => 'Properties fetched successfully',
                'meta' => [
                    'detected_location' => $detectedCity // Helpful for frontend debug
                ],
                'data' => $properties
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/properties/{id}
     */
    public function show($id)
    {
        try {
            $property = $this->propertyService->getPropertyDetails($id);

            return response()->json([
                'status' => 'success',
                'data' => $property
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Property not found',
            ], 404);
        }
    }
}