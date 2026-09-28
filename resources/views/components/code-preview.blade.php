@props([
    'title' => '',
    'id',
    'href' => '#',
    'code',
    'language' => 'blade',
    'preview' => null,
])

<div x-data="{ tabType: {{ $preview !== null ? "'preview'" : "'code'" }} }" id="{{ $id }}" class="mb-8">
    <div class="flex items-end justify-between gap-4">
        <a href="{{ $href }}" class="truncate pr-2 font-medium">
            {{ $title }}
        </a>

        <div role="tablist" class="tabs tabs-lift">
            @if($preview !== null)
                <button
                    type="button"
                    role="tab"
                    class="tab"
                    :class="{ 'tab-active': tabType === 'preview' }"
                    @click="tabType = 'preview'"
                >
                    Preview
                </button>
            @endif

            <button
                type="button"
                role="tab"
                class="tab"
                :class="{ 'tab-active': tabType === 'code' }"
                @click="tabType = 'code'"
            >
                Code
            </button>

            @if($preview !== null)
                <button
                    type="button"
                    role="tab"
                    class="tab"
                    :class="{ 'tab-active': tabType === 'render' }"
                    @click="tabType = 'render'"
                >
                    Output
                </button>
            @endif
        </div>
    </div>

    <div class="min-h-28 rounded-b-box rounded-tl-box border border-base-300 bg-base-100 p-4">
        @if($preview !== null)
            <div x-show="tabType === 'preview'" class="flex min-h-20 flex-wrap items-center justify-center gap-3">
                {!! $preview !!}
            </div>
        @endif

        <div x-show="tabType === 'code'" @if($preview !== null) x-cloak @endif>
            <x-code :$language>
{!! $code !!}
</x-code>
        </div>

        @if($preview !== null)
            <div x-show="tabType === 'render'" x-cloak>
                <x-code language="html">
{!! $preview !!}
</x-code>
            </div>
        @endif
    </div>
</div>
