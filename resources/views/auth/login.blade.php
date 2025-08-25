@extends('layouts.guest')

@section('title','Login')

@section('content')
  <div class="mx-auto max-w-md bg-white border rounded-2xl p-6">
    <h2 class="text-xl font-semibold mb-4">Login Admin</h2>

    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium mb-1" for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
               class="w-full rounded-lg border px-3 py-2" autofocus required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1" for="password">Password</label>
        <input id="password" name="password" type="password"
               class="w-full rounded-lg border px-3 py-2" required />
      </div>
      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="remember" class="rounded border-gray-300">
          Ingat saya
        </label>
        <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">Daftar</a>
      </div>
      <button class="w-full rounded-lg bg-blue-600 text-white py-2 hover:bg-blue-700">Masuk</button>
    </form>
  </div>
@endsection
