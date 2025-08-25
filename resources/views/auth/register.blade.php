@extends('layouts.guest')

@section('title','Register')

@section('content')
  <div class="mx-auto max-w-md bg-white border rounded-2xl p-6">
    <h2 class="text-xl font-semibold mb-4">Buat Akun Admin</h2>

    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium mb-1" for="name">Nama</label>
        <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-lg border px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="password">Password</label>
        <input id="password" type="password" name="password" class="w-full rounded-lg border px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="password_confirmation">Konfirmasi Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded-lg border px-3 py-2" required />
      </div>
      <button class="w-full rounded-lg bg-blue-600 text-white py-2 hover:bg-blue-700">Daftar</button>
    </form>
  </div>
@endsection
