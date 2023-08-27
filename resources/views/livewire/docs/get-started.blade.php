<div>
    Requirements
    <ul>
        @foreach ($this::REQUIREMENTS as $label => $href)
            <li>
                <x-lazy-link :$href target="_blank" rel="noopener noreferrer" :$label hover primary
                             class="hover:after:content-['_↗']"/>
            </li>
        @endforeach
    </ul>
    <x-code language="sh">
        npm install theme-change
    npm install -D sass
    </x-code>

    <x-code language="sh">
    </x-code>

    <x-code-preview title="test">
        &lt;x-lazy-loading /&gt;
    </x-code-preview>

</div>
