<article class="docs-prose">
    <div class="mb-10">
        <div class="badge badge-primary badge-outline mb-3">Guide</div>
        <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $guide['title'] }}</h1>
        <p class="mt-4 max-w-3xl text-lg opacity-70">{{ $guide['description'] }}</p>
    </div>

    @switch($page)
        @case('getting-started')
            <h2>Requirements</h2>
            <ul class="list-disc space-y-2 pl-6">
                <li>PHP 8.2 or newer</li>
                <li>Laravel 12 or 13</li>
                <li>Livewire 4.4+</li>
                <li>Tailwind CSS 4</li>
                <li>daisyUI 5</li>
            </ul>

            <h2>Install</h2>
            <pre class="docs-code"><code>composer require step2dev/lazy-ui:"dev-2.x-dev" -W
php artisan lazy-ui:install

npm install
npm run build</code></pre>

            <p>The installer publishes the Lazy UI configuration and prepares the frontend entrypoints. If the application already has its own Tailwind pipeline, keep the existing entrypoint and import Lazy UI into it.</p>

            <h2>CSS entrypoint</h2>
            <pre class="docs-code"><code>@import "tailwindcss";
@import "../../vendor/step2dev/lazy-ui/resources/css/lazy.css";

@source "../views/**/*.blade.php";
@source "../js/**/*.js";
@source "../../app/**/*.php";
@source "../../vendor/step2dev/lazy-ui/src/**/*.php";
@source "../../vendor/step2dev/lazy-ui/resources/views/**/*.blade.php";

@plugin "@tailwindcss/forms" {
    strategy: "class";
}

@plugin "daisyui" {
    themes: light --default, dark --prefersdark;
}</code></pre>

            <h2>JavaScript entrypoint</h2>
            <pre class="docs-code"><code>import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import '../../vendor/step2dev/lazy-ui/resources/js/lazy.js';

window.Alpine ??= Alpine;
window.Livewire ??= Livewire;

Livewire.start();</code></pre>

            <h2>Use a component</h2>
            <pre class="docs-code"><code>&lt;x-lazy-btn primary&gt;Save&lt;/x-lazy-btn&gt;

&lt;x-lazy-alert success message="Saved" /&gt;

&lt;x-lazy-input name="email" type="email" /&gt;</code></pre>

            <p>Lazy UI keeps standard HTML attributes available on components, so classes, ARIA attributes, Alpine directives and Livewire directives can be passed directly when the component supports forwarded attributes.</p>
            @break

        @case('upgrade')
            <h2>1.x → 2.x checklist</h2>
            <ol class="list-decimal space-y-3 pl-6">
                <li>Upgrade the application to Laravel 12 or 13.</li>
                <li>Upgrade Livewire to 4.4 or newer.</li>
                <li>Require <code>step2dev/lazy-ui:dev-2.x-dev</code>.</li>
                <li>Migrate the build to Tailwind CSS 4 and <code>@tailwindcss/postcss</code>.</li>
                <li>Upgrade daisyUI to 5.</li>
                <li>Upgrade Quill to 2 if Rich Text is used.</li>
                <li>Replace the old SCSS Lazy UI entrypoint with the CSS-first entrypoint.</li>
                <li>Make sure application and vendor Blade/PHP sources are listed with <code>@source</code>.</li>
                <li>Rebuild assets and test interactive components.</li>
            </ol>

            <h2>Composer</h2>
            <pre class="docs-code"><code>composer require step2dev/lazy-ui:"dev-2.x-dev" livewire/livewire:"^4.4" -W</code></pre>

            <h2>Frontend packages</h2>
            <pre class="docs-code"><code>npm install -D tailwindcss@^4 @tailwindcss/postcss@^4 daisyui@^5 @tailwindcss/forms
npm install quill@^2.0.3</code></pre>

            <h2>Removed legacy build files</h2>
            <p>Lazy UI 2.x does not require the old <code>resources/scss/lazy.scss</code> or JavaScript Tailwind configuration used by the 1.x setup. Tailwind 4 configuration lives in CSS.</p>

            <h2>Compatibility aliases</h2>
            <p>Lazy UI keeps compatibility helpers for several daisyUI 4 → 5 changes, including legacy masks and older semantic aliases where practical. New projects should use current daisyUI 5 semantics.</p>
            @break

        @case('themes')
            <h2>Enable themes</h2>
            <p>Define the themes you want Tailwind/daisyUI to compile in your CSS entrypoint.</p>
            <pre class="docs-code"><code>@plugin "daisyui" {
    themes: light --default, dark --prefersdark, cupcake, business, night;
}</code></pre>

            <h2>Configure Lazy UI</h2>
            <pre class="docs-code"><code>// config/lazy/themes.php
return [
    'theme_toggle' => 'multiple',
    'themes' => ['light', 'dark', 'cupcake', 'business', 'night'],
    'toggle_themes' => ['light', 'dark'],
];</code></pre>

            <h2>Theme switcher</h2>
            <div class="my-6 rounded-box border border-base-300 p-6">
                <x-lazy-theme-switcher />
            </div>
            <pre class="docs-code"><code>&lt;x-lazy-theme-switcher /&gt;</code></pre>

            <p>The selected theme is applied through the document <code>data-theme</code> attribute and persisted in local storage. The multi-theme dropdown uses daisyUI 5's explicit <code>dropdown-open</code> state.</p>
            @break

        @case('livewire')
            <h2>Livewire 4</h2>
            <p>Lazy UI 2.x targets Livewire 4.4+ while keeping components usable in ordinary Blade templates.</p>

            <h2>Two-way binding</h2>
            <pre class="docs-code"><code>&lt;x-lazy-form-input wire:model="name" label="Name" /&gt;
&lt;x-lazy-form-toggle wire:model.live="enabled" label="Enabled" /&gt;
&lt;x-lazy-richtext wire:model="body" /&gt;</code></pre>

            <h2>File uploads</h2>
            <pre class="docs-code"><code>use Livewire\WithFileUploads;

class ProfileForm extends Component
{
    use WithFileUploads;

    public $photo;
}</code></pre>
            <pre class="docs-code"><code>&lt;x-lazy-form-image wire:model="photo" label="Photo" /&gt;</code></pre>

            <h2>Runtime boot</h2>
            <p>If your application bundles Livewire's ESM build itself, call <code>Livewire.start()</code> once after registering Lazy UI's JavaScript. Do not start Livewire twice.</p>
            @break
    @endswitch
</article>
