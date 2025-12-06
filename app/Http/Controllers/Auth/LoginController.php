<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // Get intended URL but filter out problematic endpoints (streams, API calls)
        $intended = session()->pull('url.intended', $this->redirectPath());

        // If intended URL is a stream or messages endpoint, redirect to dashboard instead
        if (str_contains($intended, '/stream') || str_contains($intended, '/messages')) {
            $intended = $this->redirectPath();
        }

        return redirect($intended);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function redirectPath(): string
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return route('filament.admin.pages.dashboard');
        }

        if ($user->isWorker()) {
            return route('filament.worker.pages.dashboard');
        }

        return route('customer.dashboard');
    }
}
