@props([
    'title' => '',
    'id',
    'href' => '#',
    'code',
    'language' => 'blade',
    'preview',
])
<div x-data="{ tabType: 'preview' }" id="{{ $id }}" class="mb-2">
    <div class="flex justify-between items-center">
        <a href="{{ $href }}" class="truncate font-medium pr-2">
            {{ $title }}
        </a>
        <x-lazy-tabs size="lg" type="lifted">
            <x-lazy-tab
                x-bind:class="tabType === 'preview' && 'tab-active'"
                x-on:click="tabType = 'preview'"
            >
                Preview
            </x-lazy-tab>
            <x-lazy-tab
                x-bind:class="tabType === 'code' &&  'tab-active'"
                x-on:click="tabType = 'code'"
            >
                Code
            </x-lazy-tab>
            <x-lazy-tab
                x-bind:class="tabType === 'render' && 'tab-active'"
                x-on:click="tabType = 'render'"
            >
                Output
            </x-lazy-tab>
        </x-lazy-tabs>
    </div>
    <div class="border-base-300 bg-base-100 rounded-b-box rounded-tl-box flex min-h-[6rem] min-w-[18rem] flex-wrap items-center justify-center gap-2 overflow-x-hidden border bg-cover bg-top p-4 undefined mt-[-1px]">
        <div x-bind:class="tabType === 'preview' || 'hidden'">
            {!! $preview !!}
        </div>
        <div x-bind:class="tabType === 'code' || 'hidden' " class="w-full">
            <x-code :$language>
                {!! $code !!}
            </x-code>
        </div>
        <div x-bind:class="tabType === 'render' || 'hidden' " class="w-full">
            <x-code :$language>
                {!! $preview !!}
            </x-code>
        </div>
    </div>
</div>
