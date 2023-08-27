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
                <div class="pr-2">LazyUi</div>
                <x-lazy-breadcrumbs>
                    <ul>
                        <li>
                            <a href="/">Home</a>
                        </li>
                        <li>
                            <a href="/docs">
                                Docs
                            </a>
                        </li>
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
            aside
        </aside>
        <div class="w-4/6">
            {{ $slot }}
        </div>
        <aside class="w-1/6 bg-base-200/5 p-4">
            aside
        </aside>
    </main>

    <x-slot:footer>
        <footer class="bg-base-200/5 p-4">
            footer
        </footer>
    </x-slot:footer>
</x-layouts.guest>
