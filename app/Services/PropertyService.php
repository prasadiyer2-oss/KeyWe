<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyService
{
    /**
     * Get a paginated list of properties with filters
     */
    public function listProperties(Request $request)
    {
        // 1. Start the query
        $query = Property::query();

        // 2. Eager Load Relationships (Optimize performance)
        // 'project': Gets the parent project details
        // 'attachment': Gets the images
        // 'filterOptions': Gets tags like "2 BHK", "Ready to Move"
        $query->with(['project', 'attachment', 'filterOptions']);

        // 3. Apply Basic Filters (Optional - add more as needed)
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        // 4. Return Paginated Result
        // paginate(10) automatically handles ?page=1, ?page=2
        return $query->latest()->paginate(10);
    }

    /**
     * Get a single property by ID
     */
    public function getPropertyDetails($id)
    {
        return Property::with(['project', 'attachment', 'filterOptions'])
            ->findOrFail($id);
    }
}