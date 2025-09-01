<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
{
    $data = $request->validate([
        'name'     => ['required','string','max:255'],
        'email'    => ['required','string','email','max:255','unique:users,email'],
        'password' => ['required','confirmed', Rules\Password::defaults()],
        'role'     => ['nullable','in:admin,user'], // <- tambahkan validasi role
    ]);

    $role = in_array($request->input('role'), ['admin','user'], true)
        ? $request->input('role')
        : 'user';

    User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
        'role'     => $role, // <- SIMPAN role ke DB
    ]);

    return redirect()->route('login')->with('status','Registrasi berhasil! Silakan login.');
}
}
