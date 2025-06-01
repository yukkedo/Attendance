<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('title')
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <a class="header__link" href="/attendance"><img class="header__img" src="{{ asset('image/logo.png') }}" alt="ロゴ"></a>
        @yield('header')
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>