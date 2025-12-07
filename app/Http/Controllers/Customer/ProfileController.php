<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('customer.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request, ImageService $imageService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:20480'], // 20MB max
        ]);

        $user = auth()->user();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Process image: resize to 600x600 and 200x200, delete original
            $path = $imageService->processAvatar(
                $request->file('avatar'),
                $user->avatar_path
            );
            $user->avatar_path = $path;
        }

        $user->name = $validated['name'];
        $user->save();

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

    public function deleteAvatar(ImageService $imageService): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            // Delete both 600px and 200px versions
            $imageService->deleteAvatarFiles($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Profile photo removed.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('customer.profile.edit')
            ->with('password_success', 'Password updated successfully!');
    }
}
