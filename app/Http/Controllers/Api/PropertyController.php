<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $propertyService;

    // Dependency Injection: Laravel automatically gives us the Service
    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * GET /api/v1/properties
     */
    public function index(Request $request)
    {
        try {
            $properties = $this->propertyService->listProperties($request);

            return response()->json([
                'status' => 'success',
                'message' => 'Properties fetched successfully',
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