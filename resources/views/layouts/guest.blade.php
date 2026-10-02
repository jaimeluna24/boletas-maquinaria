<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
     <script>
        document.documentElement.setAttribute(
            'data-theme',
            localStorage.getItem('theme') ?? 'light'
        );
    </script>
</head>
<body class="bg-base-200/0 text-base-content">

    <!-- Sin Navbar, directo a centrar el componente -->
    {{ $slot }}
  <x-alert />
</body>
</html>
