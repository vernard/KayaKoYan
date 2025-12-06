<?php

namespace App\Http\Controllers;

use App\Models\WorkerProfile;
use Illuminate\View\View;

class WorkerProfileController extends Controller
{
    public function show(WorkerProfile $profile): View
    {
        $profile->load('user');

        $listings = $profile->user->listings()
            ->with('images')
            ->active()
            ->latest()
            ->get();

        return view('worker.profile', compact('profile', 'listings'));
    }
}
