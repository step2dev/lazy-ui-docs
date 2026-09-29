<div class="min-h-screen bg-base-100 text-base-content">
    <header class="navbar mx-auto max-w-7xl px-6">
        <div class="navbar-start">
            <a href="/" class="btn btn-ghost text-xl font-black">Lazy UI <span class="badge badge-primary badge-sm">2.x</span></a>
        </div>
        <div class="navbar-end gap-2">
            <a class="btn btn-ghost btn-sm" href="https://github.com/step2dev/lazy-ui" target="_blank" rel="noopener">GitHub</a>
            <x-lazy-theme-switcher />
        </div>
    </header>

    <main>
        <section class="hero min-h-[70vh] border-y border-base-300 bg-base-200/40">
            <div class="hero-content max-w-5xl flex-col text-center">
                <div class="badge badge-primary badge-outline mb-4">Laravel 12/13 · Livewire 4 · Tailwind 4 · daisyUI 5</div>
                <h1 class="text-5xl font-black tracking-tight sm:text-7xl">Build Laravel interfaces faster.</h1>
                <p class="max-w-3xl py-6 text-lg opacity-70">
                    Lazy UI is a Blade and Livewire component library built on current Tailwind CSS and daisyUI primitives,
                    with practical compatibility helpers for existing applications.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <x-lazy-btn primary lg :href="route('docs.guide', 'getting-started')">Get started</x-lazy-btn>
                    <x-lazy-btn outline lg href="https://github.com/step2dev/lazy-ui" target="_blank">Source</x-lazy-btn>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-6xl gap-5 px-6 py-16 md:grid-cols-3">
            <x-lazy-card title="Laravel native">
                Blade components, Livewire bindings and standard HTML attributes without a separate frontend framework.
            </x-lazy-card>
            <x-lazy-card title="Current frontend stack">
                Tailwind CSS 4, daisyUI 5 and Quill 2 support are built into the 2.x migration path.
            </x-lazy-card>
            <x-lazy-card title="Complete catalog">
                The documentation covers every public component family, forms, themes, Livewire integration and upgrade guidance.
            </x-lazy-card>
        </section>
    </main>

    <footer class="border-t border-base-300 px-6 py-8 text-center text-sm opacity-70">
        step2dev/lazy-ui
    </footer>
</div>
