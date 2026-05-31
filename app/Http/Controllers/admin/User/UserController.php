<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Logins;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = Logins::query()
            ->when($request->search, fn($q) =>
                $q->where('username', 'like', '%' . $request->search . '%')
            )
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        return view('Admin.Users.user-list', compact('users'));
    }

    public function create()
    {
        return view('Admin.Users.user-form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:logins,username',
            'role'     => 'required|in:Admin,User',
            'password' => 'required|string|min:6|confirmed',
        ]);

        Logins::create([
            'username' => $request->username,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('user.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = Logins::find($id);

        if (!$user) {
            return redirect()->route('user.index')
                ->with('success', 'User not found.');
        }

        return view('Admin.Users.user-form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:logins,username,' . $id,
            'role'     => 'required|in:Admin,User',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = Logins::findOrFail($id);
        $data = [
            'username' => $request->username,
            'role'     => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = Logins::findOrFail($id);
        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'User deleted successfully.');
    }
}