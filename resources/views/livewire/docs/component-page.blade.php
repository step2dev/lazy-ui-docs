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
            <h2>Example</h2>

            <x-code-preview
                :title="$component['name'].' example'"
                :code="$component['example']"
            />

            @if($slug === 'loading')
                <h2>Variants</h2>

                <x-code-preview
                    title="Loading types"
                    :code="'<div class=&quot;flex flex-wrap gap-4&quot;>
<x-lazy-loading type=&quot;spinner&quot; />
<x-lazy-loading type=&quot;dots&quot; />
<x-lazy-loading type=&quot;ring&quot; />
<x-lazy-loading type=&quot;ball&quot; />
<x-lazy-loading type=&quot;bars&quot; />
<x-lazy-loading type=&quot;infinity&quot; />
</div>'"
                />

                <x-code-preview
                    title="Loading sizes"
                    :code="'<div class=&quot;flex items-center gap-4&quot;>
<x-lazy-loading xs />
<x-lazy-loading sm />
<x-lazy-loading md />
<x-lazy-loading lg />
</div>'"
                />

                <x-code-preview
                    title="Loading colors"
                    :code="'<div class=&quot;flex flex-wrap gap-4&quot;>
<x-lazy-loading primary />
<x-lazy-loading secondary />
<x-lazy-loading accent />
<x-lazy-loading info />
<x-lazy-loading success />
<x-lazy-loading warning />
<x-lazy-loading error />
</div>'"
                />
            @endif

            @if($slug === 'button')
                <h2>Variants</h2>

                <x-code-preview
                    title="Button variants"
                    :code="'<div class=&quot;flex flex-wrap gap-2&quot;>
<x-lazy-btn primary>Primary</x-lazy-btn>
<x-lazy-btn secondary>Secondary</x-lazy-btn>
<x-lazy-btn accent>Accent</x-lazy-btn>
<x-lazy-btn outline>Outline</x-lazy-btn>
<x-lazy-btn ghost>Ghost</x-lazy-btn>
</div>'"
                />
            @endif

            @if($slug === 'badge')
                <h2>Variants</h2>

                <x-code-preview
                    title="Badge variants"
                    :code="'<div class=&quot;flex flex-wrap gap-2&quot;>
<x-lazy-badge primary label=&quot;Primary&quot; />
<x-lazy-badge success label=&quot;Success&quot; />
<x-lazy-badge warning label=&quot;Warning&quot; />
<x-lazy-badge error label=&quot;Error&quot; />
<x-lazy-badge outline label=&quot;Outline&quot; />
</div>'"
                />
            @endif

            <h2>Properties</h2>

            @if($parameters !== [])
                <div class="overflow-x-auto rounded-box border border-base-300">
                    <table class="table table-zebra">
                        <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Default</th>
                            <th>Required</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($parameters as $parameter)
                            <tr>
                                <td><code>{{ $parameter['name'] }}</code></td>
                                <td><code>{{ $parameter['type'] }}</code></td>
                                <td><code>{{ $parameter['default'] }}</code></td>
                                <td>{{ $parameter['required'] ? 'yes' : 'no' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>This component has no public constructor properties. Use the slot and forwarded HTML attributes.</p>
            @endif

            <h2>Attributes and integration</h2>
            <p>
                Standard Blade attributes are forwarded where supported. This includes Tailwind and daisyUI classes,
                ARIA attributes, Alpine directives and Livewire directives such as <code>wire:model</code>,
                <code>wire:click</code> and <code>wire:loading</code>.
            </p>

            <h2>Version compatibility</h2>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <tbody>
                    <tr><th>Lazy UI</th><td>2.x</td></tr>
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

            @if($componentClass)
                <div class="mb-4 break-all text-xs opacity-60">{{ $componentClass }}</div>
            @endif

            <div class="flex flex-col gap-2">
                <a class="link link-primary" href="https://github.com/step2dev/lazy-ui/tree/2.x-dev/src/Components" target="_blank" rel="noopener">PHP components</a>
                <a class="link link-primary" href="https://github.com/step2dev/lazy-ui/tree/2.x-dev/resources/views" target="_blank" rel="noopener">Blade views</a>
                <a class="link link-primary" href="https://daisyui.com/components/" target="_blank" rel="noopener">daisyUI components</a>
            </div>
        </aside>
    </div>
</article>
