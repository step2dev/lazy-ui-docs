<div>
    <div class="flex flex-col items-center justify-center">
        <div class="text-9xl font-bold text-primary">Lazy UI</div>
        <div class="text-3xl font-bold text-primary">Documentation</div>
        <div class="text-3xl font-bold text-warning pb-2">
            Work in progress
        </div>
        <x-lazy-tooltip tip="Help me on GitHub" position="top" info>
            <x-lazy-btn :href="route('docs.getting-started')" warning>Getting started</x-lazy-btn>
        </x-lazy-tooltip>
        <x-lazy-tooltip tip="Help me on GitHub" position="top" info>
            <x-lazy-link href="https://github.com/step2dev/lazy-ui-docs" info>GitHub</x-lazy-link>
        </x-lazy-tooltip>
    </div>
</div>
gkt
