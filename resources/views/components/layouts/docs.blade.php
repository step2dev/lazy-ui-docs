@props([
    'title' => '',
    'description' => '',
    'canonical' => '',
    'author' => '',
    'image' => false,
])

<x-layouts.guest
    :title="$title"
    :description="$description"
    :canonical="$canonical"
    :author="$author"
    :image="$image"
>
    <x-slot:header>
        <header class="navbar bg-base-200/5 p-4">
            <div class="flex-auto items-center justify-start px-4">
                <a href="/">LazyUi</a>
                <x-lazy-breadcrumbs>
                    <ul>
                        <li>
                        </li>
                        {{--<li>
                            <a href="/docs">
                                Docs
                            </a>
                        </li>--}}
                        <li>
                            {{ $title }}
                        </li>
                    </ul>
                </x-lazy-breadcrumbs>
            </div>

            <div class="flex-none gap-2">
                <x-lazy-theme-switcher/>
            </div>
        </header>
    </x-slot:header>

    <main class="content flex flex-col md:flex-row min-h-max">
        <aside class="w-1/6 bg-base-200/5 p-4">
            <ul class="menu w-56 rounded-box">
                <h2 class="menu-title">Actions</h2>
                <li>
                    <a href="/docs" wire:navigate>
                        Docs
                    </a>
                </li>
                <h2 class="menu-title">Data display</h2>
                <li>
                    <a href="{{ route('docs.loading') }}" wire:navigate>Loading</a>
                </li>
                <h2 class="menu-title">Data input</h2>
                <li>
                    <a href="/docs" wire:navigate>
                        Docs
                    </a>
                </li>
                <h2 class="menu-title">Layout</h2>
                <li>
                    <a href="/docs" wire:navigate>
                        Docs
                    </a>
                </li>
                <h2 class="menu-title">Navigation</h2>
                <li>
                    <a href="/docs" wire:navigate>
                        Docs
                    </a>
                </li>
                <h2 class="menu-title">Mockup</h2>
                <li>
                    <a href="/docs" wire:navigate>
                        Docs
                    </a>
                </li>
            </ul>
        </aside>
        <div class="w-4/6">
            {{ $slot }}
        </div>
        <aside class="w-1/6 bg-base-200/5 p-4">

        </aside>
    </main>

    <x-slot:footer>
        <footer class="bg-base-200/5 p-4">
            <div class="text-gray-600 text-sm">
                Code highlighting provided by
                <x-lazy-link hover href="https://torchlight.dev" target="_blank" rel="nofollow">
                    Torchlight
                </x-lazy-link>
            </div>
        </footer>
    </x-slot:footer>
</x-layouts.guest>
