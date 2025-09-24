@extends('layouts.app')
@section('title','Halaman Tidak Ditemukan')
@section('content')
  <div class="max-w-xl mx-auto text-center py-16">
    <h1 class="text-3xl font-bold mb-3">404 — Tidak ditemukan</h1>
    <p class="text-gray-600">Halaman yang Anda cari tidak tersedia.</p>
    <div class="mt-6">
      <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Ke Dashboard</a>
    </div>
  </div>
@endsection
@push('body-end')
  @include('partials.toast')
  <script>toast('info','Halaman tidak ditemukan (404).');</script>
@endpush
