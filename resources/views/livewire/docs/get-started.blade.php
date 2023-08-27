<div>
    <x
    npm install theme-change


    npm install -D sass
    npm install -D tailwindcss postcss autoprefixer
    npx tailwindcss init -p

    ew
    Requirements
    <ul>
        @foreach ($this::REQUIREMENTS as $label => $href)
            <li>
                <x-lazy-link :$href target="_blank" rel="noopener noreferrer" :$label hover primary class="hover:after:content-['_↗']"/>
            </li>
        @endforeach
    </ul>
</div>
