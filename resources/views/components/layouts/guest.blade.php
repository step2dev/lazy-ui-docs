@props([
    'title' => '',
    'description' => '',
    'canonical' => '',
    'author' => '',
    'header' => '',
    'image' => false,
    'footer' => '',
])
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?: config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="flex flex-col min-h-screen bg-base-100/5 font-sans antialiased">
{{ $header }}
{{ $slot }}
{{ $footer }}
@stack('scripts')
</body>
</html>
