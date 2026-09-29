<article class="docs-prose">
    <div class="mb-10">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge badge-primary badge-outline">{{ $category }}</span>
            <span class="badge badge-ghost">x-lazy-{{ $docComponent['tag'] }}</span>
        </div>

        <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">{{ $docComponent['name'] }}</h1>
        <p class="mt-4 max-w-3xl text-lg opacity-70">{{ $docComponent['description'] }}</p>
    </div>

    <div class="min-w-0">
            <x-code-preview
                title="Basic example"
                :description="$docComponent['description']"
                :code="$docComponent['example']"
            />

            @foreach($examples as $example)
                <x-code-preview
                    :title="$example['title']"
                    :description="$example['description'] ?? ''"
                    :code="$example['code']"
                    :render="$example['render'] ?? true"
                />
            @endforeach

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

    </div>
</article>
