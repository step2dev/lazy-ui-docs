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
        php artisan lazy-ui:install
    </x-code>

    Add the following to your app.css file:
    <x-code language="scss">
        @import "../scss/lazy.scss";
    </x-code>

    <x-code language="js">
        import "./lazy.js";
    </x-code>

    <x-code language="sh">
        vite build
        php artisan optimize:clear
    </x-code>

    <x-code-preview title="test">
        &lt;x-lazy-loading /&gt;
    </x-code-preview>

</div>
