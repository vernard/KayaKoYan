<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Listing::with(['user.workerProfile', 'images'])->active();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $listings = $query->latest()->paginate(12);

        return view('listings.index', compact('listings'));
    }

    public function show(Listing $listing): View
    {
        $listing->load(['user.workerProfile', 'images']);

        $relatedListings = Listing::with(['user.workerProfile', 'images'])
            ->active()
            ->where('user_id', $listing->user_id)
            ->where('id', '!=', $listing->id)
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings'));
    }
}
