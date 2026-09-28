<article class="docs-prose">
    <div class="mb-10">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge badge-primary badge-outline">{{ $category }}</span>
            <span class="badge badge-ghost">x-lazy-{{ $component['tag'] }}</span>
        </div>

        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">{{ $component['name'] }}</h1>
        <p class="mt-4 max-w-3xl text-lg opacity-70">{{ $component['description'] }}</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="min-w-0">
            <h2>Usage</h2>
            <pre class="docs-code"><code>{{ $component['example'] }}</code></pre>

            <h2>Live preview</h2>
            <div class="my-4 min-h-28 rounded-box border border-base-300 bg-base-100 p-6">
                @switch($slug)
                    @case('button')
                        <div class="flex flex-wrap gap-2">
                            <x-lazy-btn primary>Primary</x-lazy-btn>
                            <x-lazy-btn secondary>Secondary</x-lazy-btn>
                            <x-lazy-btn outline>Outline</x-lazy-btn>
                        </div>
                        @break
                    @case('alert')
                        <x-lazy-alert success message="Lazy UI is ready." />
                        @break
                    @case('badge')
                        <div class="flex flex-wrap gap-2">
                            <x-lazy-badge primary label="Primary" />
                            <x-lazy-badge success label="Success" />
                            <x-lazy-badge outline label="Outline" />
                        </div>
                        @break
                    @case('loading')
                        <div class="flex items-center gap-5">
                            <x-lazy-loading spinner />
                            <x-lazy-loading dots />
                            <x-lazy-loading ring />
                        </div>
                        @break
                    @case('tooltip')
                        <x-lazy-tooltip tip="Lazy UI tooltip" top>
                            <x-lazy-btn>Hover me</x-lazy-btn>
                        </x-lazy-tooltip>
                        @break
                    @case('input')
                        <x-lazy-input name="preview-name" placeholder="Type here" />
                        @break
                    @case('textarea')
                        <x-lazy-textarea name="preview-bio" rows="3" placeholder="Write something" />
                        @break
                    @case('toggle')
                        <x-lazy-toggle checked />
                        @break
                    @case('checkbox')
                        <x-lazy-checkbox checked />
                        @break
                    @case('radio')
                        <x-lazy-radio checked />
                        @break
                    @case('range')
                        <x-lazy-range primary min="0" max="100" value="60" />
                        @break
                    @case('progress')
                        <x-lazy-progress primary value="65" max="100" />
                        @break
                    @case('skeleton')
                        <div class="space-y-3">
                            <x-lazy-skeleton class="h-8 w-2/3" />
                            <x-lazy-skeleton class="h-4 w-full" />
                            <x-lazy-skeleton class="h-4 w-4/5" />
                        </div>
                        @break
                    @case('kbd')
                        <div class="flex gap-2"><x-lazy-kbd>⌘</x-lazy-kbd><x-lazy-kbd>K</x-lazy-kbd></div>
                        @break
                    @case('divider')
                        <x-lazy-divider>OR</x-lazy-divider>
                        @break
                    @case('join')
                        <x-lazy-join>
                            <x-lazy-btn>One</x-lazy-btn>
                            <x-lazy-btn>Two</x-lazy-btn>
                            <x-lazy-btn>Three</x-lazy-btn>
                        </x-lazy-join>
                        @break
                    @case('theme-switcher')
                        <x-lazy-theme-switcher />
                        @break
                    @case('status')
                        <div class="flex gap-5"><x-lazy-status success /><x-lazy-status warning /><x-lazy-status error /></div>
                        @break
                    @default
                        <div class="alert alert-info">
                            <span>This component is documented with a copy-ready Blade example. Interactive previews are added only where a safe generic state exists.</span>
                        </div>
                @endswitch
            </div>

            <h2>Attributes and integration</h2>
            <p>Use normal Blade component attributes together with Tailwind classes, daisyUI classes, ARIA attributes, Alpine directives and Livewire directives. Component-specific constructor options are defined by the Lazy UI component class and remain the source of truth.</p>

            <h2>Version compatibility</h2>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <tbody>
                    <tr><th>Lazy UI</th><td>2.x development branch</td></tr>
                    <tr><th>Laravel</th><td>12 / 13</td></tr>
                    <tr><th>Livewire</th><td>4.4+</td></tr>
                    <tr><th>Tailwind CSS</th><td>4.x</td></tr>
                    <tr><th>daisyUI</th><td>5.x</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="h-fit rounded-box border border-base-300 bg-base-200/40 p-5 xl:sticky xl:top-24">
            <h3 class="mt-0 text-base">Reference</h3>
            <div class="mt-3 flex flex-col gap-2">
                <a class="link link-primary" href="https://github.com/step2dev/lazy-ui/tree/2.x-dev/src/Components" target="_blank" rel="noopener">PHP components</a>
                <a class="link link-primary" href="https://github.com/step2dev/lazy-ui/tree/2.x-dev/resources/views" target="_blank" rel="noopener">Blade views</a>
                <a class="link link-primary" href="https://daisyui.com/components/" target="_blank" rel="noopener">daisyUI components</a>
            </div>
        </aside>
    </div>
</article>
