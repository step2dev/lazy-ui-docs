@props([
    'title' => 'Documentation',
    'description' => 'Lazy UI documentation',
])

<x-layouts.guest :title="$title.' · Lazy UI'" :description="$description">
    <div class="min-h-screen bg-base-100 text-base-content">
        <header class="navbar sticky top-0 z-50 border-b border-base-300 bg-base-100/95 px-4 backdrop-blur">
            <div class="navbar-start gap-3">
                <a href="{{ route('home') }}" wire:navigate class="btn btn-ghost text-xl font-black">
                    Lazy UI
                    <span class="badge badge-primary badge-sm">2.x</span>
                </a>
            </div>

            <div class="navbar-end gap-2">
                <a class="btn btn-ghost btn-sm hidden sm:inline-flex" href="https://github.com/step2dev/lazy-ui" target="_blank" rel="noopener">
                    GitHub
                </a>
                <x-lazy-theme-switcher />
            </div>
        </header>

        <div class="mx-auto grid max-w-screen-2xl grid-cols-1 lg:grid-cols-[18rem_minmax(0,1fr)]">
            <aside
                class="border-r border-base-300 bg-base-100 lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:overflow-y-auto"
                x-data
                x-init="$el.scrollTop = Number(sessionStorage.getItem('lazyui-docs-sidebar-scroll') || 0)"
                @scroll.passive="sessionStorage.setItem('lazyui-docs-sidebar-scroll', $el.scrollTop)"
            >
                <nav class="p-4">
                    <div class="mb-6">
                        <div class="mb-2 text-xs font-bold uppercase tracking-wider opacity-50">Guides</div>
                        <ul class="menu menu-sm w-full">
                            @foreach(config('docs.guides', []) as $slug => $guide)
                                <li>
                                    <a
                                        href="{{ route('docs.guide', $slug) }}"
                                        wire:navigate
                                        @class(['active' => request()->routeIs('docs.guide') && request()->route('page') === $slug])
                                    >{{ $guide['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @foreach(config('docs.categories', []) as $category => $components)
                        <div class="mb-6">
                            <div class="mb-2 text-xs font-bold uppercase tracking-wider opacity-50">{{ $category }}</div>
                            <ul class="menu menu-sm w-full">
                                @foreach($components as $slug => $component)
                                    <li>
                                        <a
                                            href="{{ route('docs.component', $slug) }}"
                                            wire:navigate
                                            @class(['active' => request()->routeIs('docs.component') && request()->route('slug') === $slug])
                                        >{{ $component['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <main class="min-w-0 px-5 py-8 sm:px-8 lg:px-12 lg:py-12">
                <div class="mx-auto max-w-5xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <footer class="border-t border-base-300 bg-base-100 px-6 py-6">
        <div class="mx-auto max-w-screen-2xl text-sm opacity-60">
            Code highlighting provided by
            <x-lazy-link hover href="https://torchlight.dev" target="_blank" rel="nofollow">
                Torchlight
            </x-lazy-link>
        </div>
    </footer>
</x-layouts.guest>
