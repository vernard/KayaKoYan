<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredListings = Listing::with(['user.workerProfile', 'images'])
            ->active()
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featuredListings'));
    }
}
