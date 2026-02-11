<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyService
{
    /**
     * Get a paginated list of properties with filters
     */
    // app/Services/PropertyService.php

    public function listProperties(Request $request, ?string $autoCity = null)
    {
        $query = Property::query();

        // Eager Load Relations
        $query->with(['project', 'attachment', 'filterOptions']);

        // 1. Manual Search (Highest Priority)
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }
        // 2. Auto-Detected Location (Fallback Priority)
        // Only apply if NO manual search was done
        elseif ($autoCity) {
            $query->where('location', 'like', "%{$autoCity}%");
        }

        // 3. Other Filters (Price, BHK, etc.)
        // ... (Your existing filter logic) ...

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