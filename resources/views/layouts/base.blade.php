<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CONNECT')</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">


    <script src="https://cdn.tailwindcss.com"></script>

    <style>
      body::before{
        content:'';position:fixed;inset:0;z-index:-1;
        background:
          radial-gradient(circle at 30% 10%,rgba(32,227,178,.25),transparent 60%),
          radial-gradient(circle at 70% 90%,rgba(32,227,178,.15),transparent 60%),
          #0e0f11;
      }
    </style>
</head>
<body class="min-h-screen flex flex-col text-white font-sans antialiased selection:bg-accent/30">
    @include('layouts.header')

    <main class="flex-1 pt-20">
        @yield('content')
    </main>

    {{-- ▼ ДОДАЙ ОЦЕ перед </body> ▼ --}}
    @stack('scripts')   {{-- якщо у дочірніх файлах будеш писати  @push --}}
    @yield('scripts')   {{-- якщо у дочірніх файлах використовуєш @section --}}
</body>
</html>

