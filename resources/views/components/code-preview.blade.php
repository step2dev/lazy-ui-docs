@props([
    'title' => '',
    'description' => '',
    'id',
    'href' => '#',
    'code',
    'language' => 'blade',
    'preview' => null,
    'output' => null,
])

<section id="{{ $id }}" class="mb-12 scroll-mt-24">
    <div class="mb-4">
        <a href="{{ $href }}" class="group inline-flex items-center gap-2 text-xl font-semibold">
            <span>{{ $title }}</span>
            <span class="opacity-0 transition-opacity group-hover:opacity-40">#</span>
        </a>

        @if($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 opacity-70">{{ $description }}</p>
        @endif
    </div>

    @if($preview !== null)
        <div class="rounded-t-box border border-base-300 bg-base-100">
            <div class="flex min-h-40 flex-wrap items-center justify-center gap-4 overflow-x-auto p-6 sm:p-8">
                {!! $preview !!}
            </div>
        </div>
    @endif

    <div @class([
        'overflow-hidden border border-base-300 bg-neutral text-neutral-content',
        'rounded-box' => $preview === null,
        'rounded-b-box border-t-0' => $preview !== null,
    ])>
        <div class="flex items-center justify-between border-b border-base-content/10 px-4 py-2 text-xs">
            <span class="font-medium uppercase tracking-wide opacity-60">Blade</span>
        </div>

        <x-code :$language>
{!! $code !!}
</x-code>
    </div>

    @if($output !== null)
        <details class="mt-3 rounded-box border border-base-300 bg-base-100">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium">Rendered HTML</summary>
            <div class="border-t border-base-300">
                <x-code language="html">
{!! $output !!}
</x-code>
            </div>
        </details>
    @endif
</section>
