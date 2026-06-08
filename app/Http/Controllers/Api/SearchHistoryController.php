<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get user's search history
    public function index(Request $request)
    {
        $user = auth()->user();

        $perPage = $request->get('per_page', 15);

        $history = SearchHistory::with('medicine')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Format the paginated data
        $history->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine->brand_name,
                'generic_name' => $item->medicine->generic_name,
                'dosage' => $item->medicine->dosage,
                'form' => $item->medicine->form,
                'searched_at' => $item->created_at->diffForHumans(),
                'searched_at_full' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json($history);
    }

    // Delete single search history
    public function destroy($id)
    {
        $user = auth()->user();

        $history = SearchHistory::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$history) {
            return response()->json(['message' => 'Search history not found'], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'Search history deleted successfully'
        ]);
    }

    // Clear all search history
    public function clearAll()
    {
        $user = auth()->user();

        SearchHistory::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'All search history cleared'
        ]);
    }
}
