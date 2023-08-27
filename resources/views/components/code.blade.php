@props([
    'language' => 'blade',
    'code' => '',
])
<pre class="mockup-code">
<x-torchlight-code language='{{ $language }}'>
    // torchlight! {"summaryCollapsedIndicator": "Click to Show"}
    {!! $code !!}
</x-torchlight-code>
</pre>
