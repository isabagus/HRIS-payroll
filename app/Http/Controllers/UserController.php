<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $users = User::with(['role', 'karyawan'])
            ->when($search, function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhereHas('role', fn($q2) => $q2->where('nama_role', 'like', "%{$search}%"))
                  ->orWhereHas('karyawan', fn($q3) => $q3->where('nama_karyawan', 'like', "%{$search}%"));
            })
            ->orderBy('ID_user', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('layouts.user.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = Role::orderBy('nama_role')->get();
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.user.create_edit', ['user' => null, 'roles' => $roles, 'karyawans' => $karyawans]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'    => 'required|string|max:50|unique:users,username',
            'password'    => 'required|string|min:6',
            'ID_role'     => 'required|exists:roles,ID_role',
            'ID_karyawan' => 'nullable|exists:karyawans,ID_karyawan',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'ID_role.required'  => 'Role wajib dipilih.',
        ]);

        User::create([
            'username'    => trim($request->username),
            'password'    => Hash::make($request->password),
            'ID_role'     => $request->ID_role,
            'ID_karyawan' => $request->ID_karyawan ?: null,
        ]);

        return redirect()->route('user.index')
            ->with('success', 'Akun user baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('nama_role')->get();
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.user.create_edit', compact('user', 'roles', 'karyawans'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username'    => 'required|string|max:50|unique:users,username,' . $user->ID_user . ',ID_user',
            'password'    => 'nullable|string|min:6',
            'ID_role'     => 'required|exists:roles,ID_role',
            'ID_karyawan' => 'nullable|exists:karyawans,ID_karyawan',
        ]);

        $data = [
            'username'    => trim($request->username),
            'ID_role'     => $request->ID_role,
            'ID_karyawan' => $request->ID_karyawan ?: null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->ID_user === auth()->id()) {
            return redirect()->route('user.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
