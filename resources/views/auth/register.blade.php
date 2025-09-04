@extends('layouts.public')

@section('title','Register • Infranet')

@section('content')
<section class="relative min-h-screen overflow-hidden flex items-center justify-center 
  bg-[radial-gradient(1200px_600px_at_-10%_-20%,#c7d2fe_0%,transparent_60%),radial-gradient(1200px_600px_at_110%_120%,#bbf7d0_0%,transparent_60%)]
  from-indigo-50 via-white to-emerald-50 px-4">

  {{-- dekorasi --}}
  <div class="pointer-events-none absolute -top-40 -left-40 h-[38rem] w-[38rem] rounded-full 
              bg-gradient-to-br from-indigo-300/50 via-fuchsia-300/40 to-emerald-300/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-48 -right-48 h-[42rem] w-[42rem] rounded-full 
              bg-gradient-to-tr from-emerald-300/50 via-sky-300/40 to-indigo-300/40 blur-3xl"></div>

  <div class="relative w-full max-w-md">
    <div class="rounded-2xl bg-white/70 backdrop-blur-xl shadow-xl ring-1 ring-black/5 p-6 md:p-8">
      <div class="text-center">
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Buat Akun di 
          <span class="bg-gradient-to-r from-indigo-600 via-sky-600 to-emerald-600 bg-clip-text text-transparent">
            Infranet
          </span>
        </h1>
        <p class="mt-2 text-slate-700/90">Daftar untuk mulai menggunakan portal aset & analitik.</p>
      </div>

      <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        {{-- Name --}}
        <div>
          <x-input-label for="name" :value="__('Name')" class="text-slate-900 dark:text-slate-900" />
          <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus
              class="block mt-1 w-full rounded-xl 
                     border-2 border-white focus:border-white 
                     bg-white/80 dark:bg-white 
                     text-slate-900 dark:text-slate-900
                     ring-0 focus:ring-0 shadow-sm" />
          <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
          <x-input-label for="email" :value="__('Email')" class="text-slate-900 dark:text-slate-900" />
          <x-text-input id="email" type="email" name="email" :value="old('email')" required
              class="block mt-1 w-full rounded-xl 
                     border-2 border-white focus:border-white 
                     bg-white/80 dark:bg-white 
                     text-slate-900 dark:text-slate-900
                     ring-0 focus:ring-0 shadow-sm" />
          <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
          <x-input-label for="password" :value="__('Password')" class="text-slate-900 dark:text-slate-900" />
          <x-text-input id="password" type="password" name="password" required autocomplete="new-password"
              class="block mt-1 w-full rounded-xl 
                     border-2 border-white focus:border-white 
                     bg-white/80 dark:bg-white 
                     text-slate-900 dark:text-slate-900
                     ring-0 focus:ring-0 shadow-sm" />
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div>
          <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-900 dark:text-slate-900" />
          <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
              class="block mt-1 w-full rounded-xl 
                     border-2 border-white focus:border-white 
                     bg-white/80 dark:bg-white 
                     text-slate-900 dark:text-slate-900
                     ring-0 focus:ring-0 shadow-sm" />
          <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
          <a href="{{ route('login') }}"
             class="underline text-sm text-slate-600 hover:text-slate-900">
             {{ __('Already registered?') }}
          </a>

          <x-primary-button class="rounded-xl bg-slate-900 hover:bg-black">
            {{ __('Register') }}
          </x-primary-button>
        </div>
      </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-600/80">
      © {{ date('Y') }} Divisi • Infranet
    </p>
  </div>
</section>
@endsection
