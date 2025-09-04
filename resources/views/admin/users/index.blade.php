@extends('layouts.app')
@section('title','Manajemen User')

@section('content')
  <h2 class="text-2xl font-bold mb-4">Manajemen User</h2>

  <div class="bg-white rounded-xl border overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left">Nama</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Role</th>
          <th class="px-4 py-2 text-left">Permissions</th>
          <th class="px-4 py-2 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $u)
          @php
            // Role & permission via Spatie (gabungan role + direct)
            $roleNames = $u->getRoleNames();                 // Collection of role names
            $permCount = $u->getAllPermissions()->count();   // gabungan via role + direct
          @endphp
          <tr class="border-t">
            <td class="px-4 py-2">{{ $u->name }}</td>
            <td class="px-4 py-2">{{ $u->email }}</td>

            {{-- Role --}}
            <td class="px-4 py-2">
              {{ $roleNames->join(', ') ?: '-' }}
            </td>

            {{-- Permissions (jumlah total) --}}
            <td class="px-4 py-2">
              @if($permCount === 0)
                <span class="text-gray-500">-</span>
              @else
                <span class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1.5 bg-gray-50">
                  <span class="font-medium">{{ $permCount }}</span>
                  <span class="text-gray-600">izin</span>
                </span>
              @endif
            </td>

            <td class="px-4 py-2 text-center space-x-2">
              <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Atur</a>
              @if(auth()->id() !== $u->id)
                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?');">
                  @csrf @method('DELETE')
                  <button class="inline-flex px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">Hapus</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $users->links() }}
  </div>
@endsection