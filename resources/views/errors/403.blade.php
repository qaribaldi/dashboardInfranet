@extends('layouts.app')
@section('title','Tidak Diizinkan')
@section('content')
  <div class="max-w-xl mx-auto text-center py-16">
    <h1 class="text-3xl font-bold mb-3">403 — Akses ditolak</h1>
    <p class="text-gray-600">Anda tidak punya izin untuk membuka halaman ini.</p>
    <div class="mt-6">
      <a href="{{ url()->previous() ?: route('dashboard') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>
@endsection
@push('body-end')
  @include('partials.toast')
  <script>toast('warning','Anda tidak punya izin (403).');</script>
@endpush
