<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\DeletionRequest;

class ProfileController extends Controller
{
    /**
     * Show the profile settings page.
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.itemMenu.setting', compact('user'));
    }

    /**
     * Update account info (name, username, email, phone, role, status).
     * Uses $user->getTable() so it works with any table name (e.g. "logins").
     */
    public function updateInfo(Request $request)
    {
        $user  = Auth::user();
        $table = $user->getTable(); // resolves to "logins" (or whatever your model uses)

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:' . $table . ',username,' . $user->id,
            'email'    => 'required|email|max:255|unique:' . $table . ',email,' . $user->id,
            'phone'    => 'nullable|string|max:50',
            'role'     => 'nullable|in:admin,manager,staff',
            'status'   => 'nullable|in:active,inactive',
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => $request->role   ?? $user->role,
            'status'   => $request->status ?? $user->status,
        ]);

        return redirect()->route('setting.index', ['tab' => 'profile'])
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update avatar photo.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()->route('setting.index', ['tab' => 'profile'])
            ->with('success', 'Avatar updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password'          => 'required',
            'new_password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'new_password_confirmation' => 'required',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('setting.index', ['tab' => 'profile'])
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Request deletion of the authenticated user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Check if user already has a pending request
        if (DeletionRequest::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            return back()->withErrors(['error' => 'You already have a pending deletion request.']);
        }

        // Create deletion request
        DeletionRequest::create([
            'user_id' => $user->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('setting.index', ['tab' => 'profile'])
            ->with('success', 'Your account deletion request has been submitted. An admin will review it soon.');
    }
}