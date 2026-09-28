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

    <title>{{ $title ?: 'Lazy UI' }}</title>
    <meta name="description" content="{{ $description ?: 'Laravel Blade and Livewire components powered by Tailwind CSS and daisyUI.' }}">
    @if($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-base-100 font-sans antialiased">
{{ $header }}
{{ $slot }}
{{ $footer }}
@livewireScriptConfig
@stack('scripts')
</body>
</html>
