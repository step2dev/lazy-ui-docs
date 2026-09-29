@props([
    'language' => 'blade',
    'code' => '',
])

<pre class="mockup-code w-full overflow-x-auto"><x-torchlight-code language='{{ $language }}'>{!! trim($code) !!}</x-torchlight-code></pre>
