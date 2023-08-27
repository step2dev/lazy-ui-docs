@props([
    'language' => 'blade',
    'code' => '',
])
<pre class="mockup-code w-full">
<x-torchlight-code language='{{ $language }}'>
    // torchlight! {"summaryCollapsedIndicator": "Click to Show"}
    {!! $code !!}
</x-torchlight-code>
</pre>
