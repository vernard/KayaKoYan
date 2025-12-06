<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $recentOrders = $user->customerOrders()
            ->with(['listing.images', 'worker'])
            ->latest()
            ->take(5)
            ->get();

        $activeOrders = $user->customerOrders()
            ->active()
            ->count();

        $completedOrders = $user->customerOrders()
            ->completed()
            ->count();

        return view('customer.dashboard', compact('recentOrders', 'activeOrders', 'completedOrders'));
    }
}
