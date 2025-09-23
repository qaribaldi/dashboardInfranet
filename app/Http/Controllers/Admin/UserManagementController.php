<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with(['roles','permissions'])->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(function ($p) {
                $parts = explode('.', $p->name);
                return $parts[0] ?? 'other';
            });

        return view('admin.users.edit', compact('user','roles','permissions'));
    }

    public function update(Request $request, User $user)
{
    $data = $request->validate([
        'role' => ['nullable','in:admin,user'],
        'permissions' => ['array'],
        'permissions.*' => ['string'],
    ]);

    $perms = $data['permissions'] ?? [];

    // === VALIDASI: jika aktifkan dashboard.view.history, wajib pilih minimal satu tipe aset ===
    if (in_array('dashboard.view.history', $perms, true)) {
        $typePerms = [
            'dashboard.history.pc',
            'dashboard.history.printer',
            'dashboard.history.proyektor',
            'dashboard.history.ac',
        ];
        if (empty(array_intersect($perms, $typePerms))) {
            return back()
                ->withInput()
                ->withErrors([
                    'permissions' => 'Jika mengaktifkan Dashboard History, pilih minimal satu tipe aset (PC/Printer/Proyektor/AC).',
                ]);
        }
    }

    // Sinkron role (opsional)
    if (!empty($data['role'])) {
        $user->syncRoles([$data['role']]);
        if (in_array($data['role'], ['admin','user'], true)) {
            $user->role = $data['role']; // sinkron ke kolom role lama jika ada
            $user->save();
        }
    }

    // Sinkron permission
    $user->syncPermissions($perms);

    return redirect()->route('admin.users.index')->with('success','Izin user diperbarui.');
}


    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error','Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('success','User dihapus.');
    }
}
