<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','CONNECT')</title>

    <!-- Tailwind CDN (один раз для всіх сторінок) -->
    <script src="https://cdn.tailwindcss.com"></script>

    @yield('head')   {{-- додаткові теги конкретної сторінки --}}
</head>
<body class="bg-base text-white antialiased">

    @yield('content')  {{-- головний вміст кожної сторінки --}}

    @yield('scripts')  {{-- JS, який потрібен тільки цій сторінці --}}
</body>
</html>
