<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\DeletionRequest;
use App\Models\ResourceDeletionRequest;
use App\Models\Logins;
use App\Services\ResourceDeletionRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function pageSetting(Request $request)
    {
        $user = Auth::user();
        $tab  = $request->query('tab', 'general');

        return view('Admin.Settings.setting', compact('user', 'tab'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'nullable|string|max:255',
            'tagline'         => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:500',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string|max:500',
            'currency'        => 'nullable|string|max:20',
            'timezone'        => 'nullable|string|max:100',
            'logo_text'       => 'nullable|string|max:100',
            'delivery_time'   => 'nullable|string|max:20',
            'total_dishes'    => 'nullable|string|max:20',
            'happy_customers' => 'nullable|string|max:20',
            'rating'          => 'nullable|string|max:10',
            'hero_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('hero_image')) {
            $old = Setting::get('hero_image');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('hero_image')->store('settings', 'public');
            Setting::set('hero_image', $path);
        }

        $fields = [
            'restaurant_name', 'tagline', 'description', 'phone', 'email',
            'address', 'currency', 'timezone', 'logo_text',
            'delivery_time', 'total_dishes', 'happy_customers', 'rating',
        ];

        foreach ($fields as $key) {
            Setting::set($key, $request->input($key));
        }

        return redirect()->route('setting.index')
            ->with('success', 'Settings saved successfully.');
    }

    public function deleteImage()
    {
        $old = Setting::get('hero_image');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
        Setting::set('hero_image', null);

        return redirect()->route('setting.index')
            ->with('success', 'Hero image removed.');
    }

    public function deletionRequests()
    {
        $accountDeletionRequests = DeletionRequest::with('user')
            ->latest()
            ->paginate(10, ['*'], 'account_page');

        $resourceDeletionRequests = ResourceDeletionRequest::with(['requester', 'approver'])
            ->latest()
            ->paginate(10, ['*'], 'resource_page');

        return view('Admin.Settings.deletion-requests', compact('accountDeletionRequests', 'resourceDeletionRequests'));
    }

    public function approveDeletion($id)
    {
        $request = DeletionRequest::findOrFail($id);
        $request->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $user = $request->user;
        if ($user) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
        }

        return redirect()->route('deletion-requests.index')
            ->with('success', 'Deletion request approved and user account deleted.');
    }

    public function denyDeletion($id)
    {
        $request = DeletionRequest::findOrFail($id);
        $request->update([
            'status'      => 'denied',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('deletion-requests.index')
            ->with('success', 'Deletion request denied.');
    }

    public function deleteAllApproved()
    {
        $approved = DeletionRequest::where('status', 'approved')->get();

        foreach ($approved as $request) {
            $user = $request->user;
            if ($user) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->delete();
            }
            $request->delete();
        }

        return redirect()->route('deletion-requests.index')
            ->with('success', 'All approved deletion requests and their accounts have been deleted.');
    }

    public function approveResourceDeletion($id)
    {
        $request = ResourceDeletionRequest::findOrFail($id);

        if ($request->status === 'pending') {
            app(ResourceDeletionRequestService::class)->approve($request, Auth::id());
        }

        return redirect()->route('deletion-requests.index')
            ->with('success', 'Resource deletion request approved.');
    }

    public function denyResourceDeletion($id)
    {
        $request = ResourceDeletionRequest::findOrFail($id);
        $request->update([
            'status' => 'denied',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('deletion-requests.index')
            ->with('success', 'Resource deletion request denied.');
    }

    public function deleteAllApprovedResourceRequests()
    {
        ResourceDeletionRequest::where('status', 'approved')->delete();

        return redirect()->route('deletion-requests.index')
            ->with('success', 'All approved resource deletion requests have been cleared.');
    }
}
