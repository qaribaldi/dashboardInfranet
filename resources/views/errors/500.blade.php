@extends('layouts.app')

@section('title','Kesalahan Server')

@section('content')
  <div class="max-w-xl mx-auto text-center py-16">
    <h1 class="text-3xl font-bold mb-3">500 — Kesalahan Server</h1>
    <p class="text-gray-600">Maaf, terjadi kesalahan di sistem. Coba muat ulang atau kembali nanti.</p>
    <div class="mt-6">
      <a href="{{ url()->previous() ?: route('dashboard') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>
@endsection

@push('body-end')
  @include('partials.toast')
  <script>
    toast('error', 'Terjadi kesalahan internal (500). Tim kami akan memeriksanya.', { duration: 7000 });
  </script>
@endpush
