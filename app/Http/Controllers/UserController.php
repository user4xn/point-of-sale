<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    { 
        $users = User::paginate(10);
        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,cashier',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $data['password'] = bcrypt($data['password']);
                User::create($data);
            });

            return redirect()->route('users.index')->with([
                'flash' => ['success' => 'Pengguna Telah Ditambahkan'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error saat menambahkan user: '.$e->getMessage());

            return redirect()->back()->with([
                'flash' => ['error' => 'Terjadi kesalahan saat menambahkan pengguna'],
            ]);
        }
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,cashier',
        ]);

        try {
            DB::transaction(function () use ($data, $user) {
                if (!empty($data['password'])) {
                    $data['password'] = bcrypt($data['password']);
                } else {
                    unset($data['password']);
                }
                $user->update($data);
            });

            return redirect()->route('users.index')->with([
                'flash' => ['success' => 'Pengguna Telah Diubah'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error saat mengubah user: '.$e->getMessage());

            return redirect()->back()->with([
                'flash' => ['error' => 'Terjadi kesalahan saat mengubah pengguna'],
            ]);
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                $user->delete();
            });

            return redirect()->route('users.index')->with([
                'flash' => ['success' => 'Pengguna Telah Dihapus'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error saat menghapus user: '.$e->getMessage());

            return redirect()->back()->with([
                'flash' => ['error' => 'Terjadi kesalahan saat menghapus pengguna'],
            ]);
        }
    }
}
