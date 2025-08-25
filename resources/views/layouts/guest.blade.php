<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>@yield('title','Infranet')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="border-b bg-white">
    <div class="mx-auto max-w-5xl px-4 py-3 flex items-center justify-between">
      <a href="{{ route('landing') }}" class="font-semibold">Aset IT Infranet</a>
      <nav class="flex items-center gap-3 text-sm">
        @auth
          <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded hover:bg-gray-100">Dashboard</a>
          <form action="{{ route('logout') }}" method="POST">@csrf
            <button class="px-3 py-1.5 rounded border hover:bg-gray-50">Logout</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="px-3 py-1.5 rounded hover:bg-gray-100">Login</a>
          <a href="{{ route('register') }}" class="px-3 py-1.5 rounded border hover:bg-gray-50">Register</a>
        @endauth
      </nav>
    </div>
  </header>

  <main class="mx-auto max-w-5xl px-4 py-10">
    @yield('content')
  </main>
</body>
</html>
