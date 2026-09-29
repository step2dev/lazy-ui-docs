<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class ComponentPage extends Component
{
    public string $slug;

    public string $category;

    public array $docComponent = [];

    public ?string $componentClass = null;

    public array $parameters = [];

    public array $examples = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        foreach (config('docs.categories', []) as $category => $components) {
            if (! isset($components[$slug])) {
                continue;
            }

            $this->category = $category;
            $this->docComponent = $components[$slug];
            $this->componentClass = $this->resolveComponentClass($this->docComponent['tag']);
            $this->parameters = $this->resolveParameters($this->componentClass);
            $this->examples = $this->resolveExamples($slug);

            return;
        }

        abort(404);
    }

    public function render(): View
    {
        return view('livewire.docs.component-page')
            ->layout('components.layouts.docs', [
                'title' => $this->docComponent['name'],
                'description' => $this->docComponent['description'],
            ]);
    }


    private function resolveExamples(string $slug): array
    {
        $examples = [];
        $tag = $this->docComponent['tag'];
        $parameterNames = collect($this->parameters)->pluck('name');

        $colors = $this->supportedColors($slug);

        if ($colors !== []) {
            $examples[] = [
                'title' => 'Colors',
                'code' => collect($colors)
                    ->map(fn (string $color): string => $this->exampleTag($tag, [$color => true], ucfirst($color)))
                    ->implode("\n"),
            ];
        }

        $sizes = $this->supportedSizes($slug);

        if ($sizes !== []) {
            $examples[] = [
                'title' => 'Sizes',
                'code' => collect($sizes)
                    ->map(fn (string $size): string => $this->exampleTag($tag, [$size => true], strtoupper($size)))
                    ->implode("\n"),
            ];
        }

        foreach ($this->exhaustiveCommonExamples($slug, $tag, $parameterNames) as $example) {
            $examples[] = $example;
        }

        foreach ($this->componentSpecificExamples($slug) as $example) {
            $examples[] = $example;
        }

        return collect($examples)
            ->map(function (array $example): array {
                $example['description'] ??= $this->exampleDescription($example['title'] ?? 'Example');

                return $example;
            })
            ->all();
    }

    private function exampleDescription(string $title): string
    {
        $normalized = str($title)->lower()->toString();

        return match (true) {
            str_contains($normalized, 'size') => 'Available size variants for this component.',
            str_contains($normalized, 'color') => 'Available semantic color variants.',
            str_contains($normalized, 'position') || str_contains($normalized, 'align') => 'Available positioning and alignment options.',
            str_contains($normalized, 'type') => 'Available component type variants.',
            str_contains($normalized, 'shape') || str_contains($normalized, 'mask') => 'Available visual shape variants.',
            str_contains($normalized, 'driver') => 'Available rendering or integration drivers.',
            str_contains($normalized, 'boolean') || str_contains($normalized, 'modifier') => 'Optional boolean modifiers supported by the component.',
            str_contains($normalized, 'state') => 'Common interactive and disabled states.',
            str_contains($normalized, 'layout') => 'Available layout variants.',
            default => 'Example usage with Lazy UI Blade syntax.',
        };
    }

    private function supportedColors(string $slug): array
    {
        $standard = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];

        return match ($slug) {
            'button' => ['neutral', 'primary', 'secondary', 'accent', 'ghost', 'info', 'success', 'warning', 'error', 'danger', 'link'],
            'badge' => ['neutral', 'primary', 'secondary', 'accent', 'ghost', 'info', 'success', 'warning', 'error', 'danger'],
            'alert' => ['info', 'success', 'warning', 'error', 'danger'],
            'chat', 'tooltip', 'rating' => ['primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'],
            'input', 'select', 'textarea' => [...$standard, 'ghost', 'no-border'],
            'checkbox', 'file-input', 'otp', 'radio', 'range', 'status', 'step', 'loading', 'progress', 'radial', 'indicator', 'toggle' => $standard,
            'link' => $standard,
            default => [],
        };
    }

    private function supportedSizes(string $slug): array
    {
        return match ($slug) {
            'button', 'badge', 'aura', 'loading', 'kbd', 'checkbox', 'file-input', 'otp', 'radio', 'range',
            'rating', 'select', 'status', 'tabs', 'textarea', 'toggle', 'indicator', 'menu-list',
            'dock', 'megamenu' => ['xs', 'sm', 'md', 'lg', 'xl'],
            default => [],
        };
    }

    private function exhaustiveCommonExamples(string $slug, string $tag, \Illuminate\Support\Collection $parameterNames): array
    {
        $examples = [];

        $booleanParameters = (in_array($slug, [
            'button', 'badge', 'input', 'select', 'textarea', 'checkbox', 'radio', 'toggle',
            'file-input', 'rating', 'range', 'otp', 'loading', 'progress', 'radial', 'link',
            'status', 'skeleton',
        ], true)
            ? collect($this->parameters)->filter(fn (array $parameter): bool => str_contains($parameter['type'], 'bool'))
            : collect())
            ->pluck('name')
            ->reject(fn (string $name): bool => in_array($name, [
                'vertical', 'horizontal', 'top', 'middle', 'bottom', 'start', 'center', 'end', 'left', 'right',
            ], true))
            ->values();

        if ($booleanParameters->isNotEmpty()) {
            $examples[] = [
                'title' => 'Boolean options',
                'code' => $booleanParameters
                    ->map(fn (string $name): string => $this->exampleTag($tag, [$name => true], str($name)->headline()->toString()))
                    ->implode("\n"),
            ];
        }

        foreach ($this->attributeExamples($slug, $tag) as $example) {
            $examples[] = $example;
        }

        return $examples;
    }

    private function enumOptions(string $slug): array
    {
        return match ($slug) {
            'accordion' => [
                'type' => ['plus', 'arrow'],
            ],
            'calendar' => [
                'driver' => ['native', 'cally', 'vc', 'react-day-picker'],
            ],
            'chat' => [
                'position' => ['start', 'end', 'left', 'right'],
            ],
            'divider' => [
                'orientation' => ['vertical', 'horizontal'],
            ],
            'dropdown' => [
                'position' => ['start', 'center', 'end', 'top', 'bottom', 'left', 'right'],
            ],
            'hero' => [
                'background' => ['none', 'base-100', 'base-200', 'base-300', 'neutral', 'primary', 'secondary'],
                'align' => ['start', 'center', 'end'],
                'width' => ['none', 'sm', 'md', 'lg', 'xl', 'full'],
                'titleSize' => ['xs', 'sm', 'md', 'lg', 'xl'],
                'spacing' => ['none', 'xs', 'sm', 'md', 'lg'],
            ],
            'indicator' => [
                'horizontal' => ['start', 'center', 'end'],
                'vertical' => ['top', 'middle', 'bottom'],
            ],
            'join' => [
                'position' => ['vertical', 'horizontal'],
            ],
            'loading' => [
                'type' => ['spinner', 'dots', 'ring', 'ball', 'bars', 'infinity'],
            ],
            'rating' => [
                'mask' => ['star-2', 'star', 'heart'],
                'type' => ['star-2', 'star', 'heart'],
            ],
            'tabs' => [
                'type' => ['box', 'boxed', 'lift', 'lifted', 'border', 'bordered'],
                'placement' => ['top', 'bottom'],
            ],
            'theme-controller' => [
                'type' => ['checkbox', 'radio'],
            ],
            'tooltip' => [
                'position' => ['top', 'right', 'bottom', 'left'],
                'align' => ['start', 'center', 'end'],
            ],
            'drawer' => [
                'width' => ['xs', 'sm', 'md', 'lg', 'full'],
                'padding' => ['none', 'xs', 'sm', 'md', 'lg'],
                'background' => ['none', 'base-100', 'base-200', 'base-300', 'neutral', 'primary', 'secondary'],
            ],
            default => [],
        };
    }

    private function attributeExamples(string $slug, string $tag): array
    {
        return match ($slug) {
            'button' => [[
                'title' => 'All button modifiers',
                'code' => collect([
                    'outline', 'dash', 'soft', 'wide', 'block', 'circle', 'square', 'group', 'join', 'disabled',
                ])->map(fn (string $attribute): string => $this->exampleTag($tag, [$attribute => true], ucfirst($attribute)))->implode("\n"),
            ], [
                'title' => 'Button HTML types',
                'code' => collect(['button', 'submit', 'reset'])
                    ->map(fn (string $type): string => '<x-lazy-btn type="'.$type.'">'.ucfirst($type).'</x-lazy-btn>')
                    ->implode("\n"),
            ], [
                'title' => 'Button links and icons',
                'code' => <<<'BLADE'
<x-lazy-btn href="/docs">Link button</x-lazy-btn>
<x-lazy-btn icon="heroicon-o-plus">Left icon</x-lazy-btn>
<x-lazy-btn right-icon="heroicon-o-arrow-right">Right icon</x-lazy-btn>
<x-lazy-btn rounded>Rounded</x-lazy-btn>
<x-lazy-btn squared>Squared</x-lazy-btn>
BLADE,
            ]],
            'badge' => [[
                'title' => 'Badge modifiers',
                'code' => <<<'BLADE'
<x-lazy-badge outline>Outline</x-lazy-badge>
<x-lazy-badge dash>Dash</x-lazy-badge>
<x-lazy-badge soft>Soft</x-lazy-badge>
BLADE,
            ]],
            'alert' => [[
                'title' => 'Alert modifiers',
                'code' => <<<'BLADE'
<x-lazy-alert info message="Default" />
<x-lazy-alert info soft message="Soft" />
<x-lazy-alert info dash message="Dash" />
BLADE,
            ]],
            'link' => [[
                'title' => 'Link modifier',
                'code' => <<<'BLADE'
<x-lazy-link href="/">Default</x-lazy-link>
<x-lazy-link href="/" hover>Hover underline</x-lazy-link>
BLADE,
            ]],
            'input' => [[
                'title' => 'HTML input types',
                'code' => collect([
                    'text', 'email', 'password', 'number', 'search', 'tel', 'url', 'date', 'time', 'datetime-local',
                    'month', 'week', 'color',
                ])->map(fn (string $type): string => '<x-lazy-input type="'.$type.'" name="'.$type.'" label="'.str($type)->headline().'" />')->implode("\n"),
            ]],
            'file-input' => [[
                'title' => 'File input modes',
                'code' => <<<'BLADE'
<x-lazy-file-input name="file" />
<x-lazy-file-input name="ghost" ghost />
<x-lazy-file-input name="image" accept="image/*" />
<x-lazy-file-input name="multiple" multiple />
<x-lazy-file-input name="disabled" disabled />
BLADE,
            ]],
            'select' => [[
                'title' => 'Select special colors',
                'code' => <<<'BLADE'
<x-lazy-select color="ghost" :options="['a' => 'A', 'b' => 'B']" />
<x-lazy-select color="no-border" :options="['a' => 'A', 'b' => 'B']" />
BLADE,
            ]],
            'textarea' => [[
                'title' => 'Textarea special colors',
                'code' => <<<'BLADE'
<x-lazy-textarea color="ghost" placeholder="Ghost" />
<x-lazy-textarea color="no-border" placeholder="No border" />
BLADE,
            ]],
            'tooltip' => [[
                'title' => 'Tooltip smart attributes',
                'code' => <<<'BLADE'
<x-lazy-tooltip tip="Top" top><button class="btn">Top</button></x-lazy-tooltip>
<x-lazy-tooltip tip="Right" right><button class="btn">Right</button></x-lazy-tooltip>
<x-lazy-tooltip tip="Bottom" bottom><button class="btn">Bottom</button></x-lazy-tooltip>
<x-lazy-tooltip tip="Left" left><button class="btn">Left</button></x-lazy-tooltip>
<x-lazy-tooltip tip="Start" start><button class="btn">Start</button></x-lazy-tooltip>
<x-lazy-tooltip tip="Center" center><button class="btn">Center</button></x-lazy-tooltip>
<x-lazy-tooltip tip="End" end><button class="btn">End</button></x-lazy-tooltip>
BLADE,
            ]],
            'loading' => [[
                'title' => 'Loading smart type attributes',
                'code' => <<<'BLADE'
<x-lazy-loading spinner />
<x-lazy-loading dots />
<x-lazy-loading ring />
<x-lazy-loading ball />
<x-lazy-loading bars />
<x-lazy-loading infinity />
BLADE,
            ]],
            default => [],
        };
    }

    private function componentSpecificExamples(string $slug): array
    {
        return match ($slug) {
            'button' => [[
                'title' => 'Button styles',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-2">
    <x-lazy-btn primary>Primary</x-lazy-btn>
    <x-lazy-btn secondary>Secondary</x-lazy-btn>
    <x-lazy-btn accent>Accent</x-lazy-btn>
    <x-lazy-btn neutral>Neutral</x-lazy-btn>
    <x-lazy-btn info>Info</x-lazy-btn>
    <x-lazy-btn success>Success</x-lazy-btn>
    <x-lazy-btn warning>Warning</x-lazy-btn>
    <x-lazy-btn error>Error</x-lazy-btn>
    <x-lazy-btn outline>Outline</x-lazy-btn>
    <x-lazy-btn ghost>Ghost</x-lazy-btn>
    <x-lazy-btn rounded>Rounded</x-lazy-btn>
    <x-lazy-btn squared>Square</x-lazy-btn>
</div>
BLADE,
            ]],
            'aura' => [[
                'title' => 'Aura around a card',
                'description' => 'Aura is a wrapper effect. Put real content such as a card, button or image inside it.',
                'code' => <<<'BLADE'
<x-lazy-aura>
    <div class="card w-72 bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Default aura</h3>
            <p>Highlight important content with an animated border effect.</p>
        </div>
    </div>
</x-lazy-aura>
BLADE,
            ], [
                'title' => 'Aura styles',
                'description' => 'Each aura style is shown around the same card so the visual difference is easy to compare.',
                'code' => <<<'BLADE'
<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <x-lazy-aura type="dual">
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Dual</strong></div></div>
    </x-lazy-aura>
    <x-lazy-aura type="rainbow">
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Rainbow</strong></div></div>
    </x-lazy-aura>
    <x-lazy-aura type="holo">
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Holo</strong></div></div>
    </x-lazy-aura>
    <x-lazy-aura type="gold">
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Gold</strong></div></div>
    </x-lazy-aura>
    <x-lazy-aura type="silver">
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Silver</strong></div></div>
    </x-lazy-aura>
    <x-lazy-aura type="rainbow" glow>
        <div class="card bg-base-100 shadow"><div class="card-body"><strong>Glow</strong></div></div>
    </x-lazy-aura>
</div>
BLADE,
            ]],
            'calendar' => [[
                'title' => 'Native calendar',
                'description' => 'The native driver works without additional JavaScript dependencies.',
                'code' => <<<'BLADE'
<x-lazy-calendar driver="native" value="2026-09-29" />
BLADE,
            ], [
                'title' => 'Alternative calendar drivers',
                'description' => 'Cally, Vanilla Calendar and React Day Picker are integration targets. Their JavaScript library must also be installed by the application.',
                'code' => <<<'BLADE'
<x-lazy-calendar driver="cally" />
<x-lazy-calendar driver="vc">Vanilla Calendar mount point</x-lazy-calendar>
<x-lazy-calendar driver="react-day-picker">React Day Picker mount point</x-lazy-calendar>
BLADE,
                'render' => false,
            ]],
            'chat' => [[
                'title' => 'Chat positions',
                'code' => <<<'BLADE'
<x-lazy-chat name="Alice" message="Start message" start />
<x-lazy-chat name="Bob" message="End message" end />
<x-lazy-chat name="Alice" message="Left alias" left />
<x-lazy-chat name="Bob" message="Right alias" right />
BLADE,
            ]],
            'divider' => [[
                'title' => 'Divider orientations',
                'code' => <<<'BLADE'
<x-lazy-divider text="Vertical" vertical />
<x-lazy-divider text="Horizontal" horizontal />
<x-lazy-divider text="HR alias" hr />
BLADE,
            ]],
            'hero' => [[
                'title' => 'Hero alignments',
                'code' => <<<'BLADE'
<x-lazy-hero title="Start" description="Left aligned" align="start" />
<x-lazy-hero title="Center" description="Centered" align="center" />
<x-lazy-hero title="End" description="Right aligned" align="end" />
BLADE,
            ], [
                'title' => 'Hero backgrounds',
                'code' => <<<'BLADE'
<x-lazy-hero title="Base 100" background="base-100" />
<x-lazy-hero title="Base 200" background="base-200" />
<x-lazy-hero title="Base 300" background="base-300" />
<x-lazy-hero title="Neutral" background="neutral" />
<x-lazy-hero title="Primary" background="primary" />
<x-lazy-hero title="Secondary" background="secondary" />
BLADE,
            ], [
                'title' => 'Hero widths and title sizes',
                'code' => <<<'BLADE'
<x-lazy-hero title="Small" width="sm" title-size="sm" spacing="sm" />
<x-lazy-hero title="Medium" width="md" title-size="md" spacing="md" />
<x-lazy-hero title="Large" width="lg" title-size="lg" spacing="lg" />
<x-lazy-hero title="XL" width="xl" title-size="xl" />
<x-lazy-hero title="Full" width="full" title-size="lg" spacing="none" />
BLADE,
            ]],
            'indicator' => [[
                'title' => 'Indicator positions',
                'description' => 'The badge can be placed on all nine horizontal and vertical position combinations.',
                'code' => <<<'BLADE'
<div class="grid gap-8 sm:grid-cols-3">
    @foreach (['start', 'center', 'end'] as $horizontal)
        @foreach (['top', 'middle', 'bottom'] as $vertical)
            <div class="flex justify-center">
                <x-lazy-indicator indicator="1" :horizontal="$horizontal" :vertical="$vertical">
                    <button class="btn w-36">{{ $horizontal }} / {{ $vertical }}</button>
                </x-lazy-indicator>
            </div>
        @endforeach
    @endforeach
</div>
BLADE,
            ]],
            'join' => [[
                'title' => 'Horizontal join',
                'description' => 'Join adjacent controls into one horizontal control group.',
                'code' => <<<'BLADE'
<x-lazy-join horizontal>
    <x-lazy-btn join>Previous</x-lazy-btn>
    <x-lazy-btn join primary>Current</x-lazy-btn>
    <x-lazy-btn join>Next</x-lazy-btn>
</x-lazy-join>
BLADE,
            ], [
                'title' => 'Vertical join',
                'description' => 'Use a vertical join when related controls should be stacked.',
                'code' => <<<'BLADE'
<x-lazy-join vertical>
    <x-lazy-btn join>Profile</x-lazy-btn>
    <x-lazy-btn join>Settings</x-lazy-btn>
    <x-lazy-btn join error>Delete</x-lazy-btn>
</x-lazy-join>
BLADE,
            ]],
            'loading' => [[
                'title' => 'Loading types',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-loading type="spinner" />
    <x-lazy-loading type="dots" />
    <x-lazy-loading type="ring" />
    <x-lazy-loading type="ball" />
    <x-lazy-loading type="bars" />
    <x-lazy-loading type="infinity" />
</div>
BLADE,
            ]],
            'accordion' => [[
                'title' => 'Accordion modes',
                'code' => <<<'BLADE'
<x-lazy-accordion title="Plus accordion" type="plus">Content</x-lazy-accordion>
<x-lazy-accordion title="Arrow accordion" type="arrow">Content</x-lazy-accordion>
<x-lazy-accordion title="Opened accordion" active>Content</x-lazy-accordion>
<x-lazy-accordion title="Toggle accordion" toggle>Content</x-lazy-accordion>
BLADE,
            ]],
            'card' => [[
                'title' => 'Card styles',
                'code' => <<<'BLADE'
<div class="grid gap-4 md:grid-cols-2">
    <x-lazy-card title="Default">Card body</x-lazy-card>
    <x-lazy-card title="Bordered" bordered>Card body</x-lazy-card>
    <x-lazy-card title="Compact" compact>Card body</x-lazy-card>
    <x-lazy-card title="Hover" hover>Card body</x-lazy-card>
    <x-lazy-card title="Side" side>Card body</x-lazy-card>
    <x-lazy-card title="Image full" image-full>Card body</x-lazy-card>
</div>
BLADE,
            ]],
            'collapse' => [[
                'title' => 'Collapse styles',
                'code' => <<<'BLADE'
<x-lazy-collapse title="Arrow" arrow>Content</x-lazy-collapse>
<x-lazy-collapse title="Plus" plus>Content</x-lazy-collapse>
<x-lazy-collapse title="Opened" open>Content</x-lazy-collapse>
BLADE,
            ]],
            'dropdown' => [[
                'title' => 'Dropdown behavior',
                'description' => 'Click, hover and forced-open dropdowns use the same menu so the behavior is easy to compare.',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-dropdown label="Click">
        <a href="#">Profile</a>
        <a href="#">Settings</a>
    </x-lazy-dropdown>

    <x-lazy-dropdown label="Hover" hover>
        <a href="#">Profile</a>
        <a href="#">Settings</a>
    </x-lazy-dropdown>

    <x-lazy-dropdown label="Always open" open>
        <a href="#">Profile</a>
        <a href="#">Settings</a>
    </x-lazy-dropdown>
</div>
BLADE,
            ], [
                'title' => 'Dropdown alignment',
                'description' => 'Start, center and end align the dropdown content relative to its trigger.',
                'code' => <<<'BLADE'
<div class="grid gap-24 md:grid-cols-3">
    <x-lazy-dropdown label="Start" position="start" open>
        <a href="#">First item</a>
        <a href="#">Second item</a>
    </x-lazy-dropdown>
    <x-lazy-dropdown label="Center" position="center" open>
        <a href="#">First item</a>
        <a href="#">Second item</a>
    </x-lazy-dropdown>
    <x-lazy-dropdown label="End" position="end" open>
        <a href="#">First item</a>
        <a href="#">Second item</a>
    </x-lazy-dropdown>
</div>
BLADE,
            ]],
            'modal' => [[
                'title' => 'Modal positions',
                'code' => <<<'BLADE'
<x-lazy-modal id="modal-top" top open>Top modal</x-lazy-modal>
<x-lazy-modal id="modal-middle" middle open>Middle modal</x-lazy-modal>
<x-lazy-modal id="modal-bottom" bottom open>Bottom modal</x-lazy-modal>
<x-lazy-modal id="modal-start" start open>Start modal</x-lazy-modal>
<x-lazy-modal id="modal-end" end open>End modal</x-lazy-modal>
BLADE,
            ]],
            'mask' => [[
                'title' => 'Mask shapes',
                'description' => 'Every shape uses the same local image and a visible label, making differences easy to compare.',
                'code' => <<<'BLADE'
<div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
    @foreach ([
        'squircle', 'heart', 'hexagon', 'hexagon-2', 'decagon', 'pentagon', 'diamond', 'circle',
        'star', 'star-2', 'triangle', 'triangle-2', 'triangle-3', 'triangle-4', 'square',
        'parallelogram', 'parallelogram-2', 'parallelogram-3', 'parallelogram-4',
    ] as $shape)
        <div class="text-center">
            <x-lazy-mask :shape="$shape" class="mx-auto size-28">
                <img src="/images/docs/sample-blue.svg" alt="{{ $shape }}" class="h-full w-full object-cover" />
            </x-lazy-mask>
            <div class="mt-2 text-xs font-medium">{{ $shape }}</div>
        </div>
    @endforeach
</div>
BLADE,
            ]],
            'rating' => [[
                'title' => 'Rating modes',
                'code' => <<<'BLADE'
<x-lazy-rating name="rating-default" />
<x-lazy-rating name="rating-heart" mask="heart" />
<x-lazy-rating name="rating-half" half />
<x-lazy-rating name="rating-clearable" clearable />
<x-lazy-rating name="rating-readonly" :value="4" readonly />
BLADE,
            ]],
            'range' => [[
                'title' => 'Range modes',
                'code' => <<<'BLADE'
<x-lazy-range min="0" max="100" value="50" />
<x-lazy-range min="0" max="100" value="25" step="5" />
<x-lazy-range min="0" max="100" value="70" :steps="5" />
<x-lazy-range min="0" max="100" value="50" vertical />
BLADE,
            ]],
            'tabs' => [[
                'title' => 'Tab styles',
                'code' => <<<'BLADE'
<x-lazy-tabs type="border">
    <x-lazy-tab label="One" active />
    <x-lazy-tab label="Two" />
</x-lazy-tabs>

<x-lazy-tabs type="lift">
    <x-lazy-tab label="One" active />
    <x-lazy-tab label="Two" />
</x-lazy-tabs>

<x-lazy-tabs type="box">
    <x-lazy-tab label="One" active />
    <x-lazy-tab label="Two" />
</x-lazy-tabs>
BLADE,
            ]],
            'steps' => [[
                'title' => 'Steps layouts',
                'code' => <<<'BLADE'
<x-lazy-steps :items="['Install', 'Configure', 'Done']" :current="2" />
<x-lazy-steps :items="['Install', 'Configure', 'Done']" :current="2" vertical />
BLADE,
            ]],
            'timeline' => [[
                'title' => 'Timeline layouts',
                'code' => <<<'BLADE'
<x-lazy-timeline :items="['Start', 'Build', 'Release']" />
<x-lazy-timeline :items="['Start', 'Build', 'Release']" vertical />
<x-lazy-timeline :items="['Start', 'Build', 'Release']" compact />
<x-lazy-timeline :items="['Start', 'Build', 'Release']" box />
BLADE,
            ]],
            'toast' => [[
                'title' => 'Interactive toast',
                'description' => 'Click a button to create a real Lazy UI toast through the global toast API.',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-2">
    <button
        type="button"
        class="btn btn-success"
        onclick="window.toast?.success('Saved successfully.')"
    >
        Success toast
    </button>

    <button
        type="button"
        class="btn btn-error"
        onclick="window.toast?.error('Something went wrong.')"
    >
        Error toast
    </button>

    <button
        type="button"
        class="btn btn-info"
        onclick="window.toast?.info('Here is some information.')"
    >
        Info toast
    </button>

    <button
        type="button"
        class="btn btn-warning"
        onclick="window.toast?.warning('Please check this value.')"
    >
        Warning toast
    </button>
</div>

<x-lazy-toast />
BLADE,
            ], [
                'title' => 'Toast positions',
                'description' => 'Position flags configure where runtime notifications appear. The static alerts below make the positions easy to understand in documentation.',
                'code' => <<<'BLADE'
<div class="grid gap-4 md:grid-cols-3">
    <div class="alert">Top start</div>
    <div class="alert">Top center</div>
    <div class="alert">Top end</div>
    <div class="alert">Middle start</div>
    <div class="alert">Middle center</div>
    <div class="alert">Middle end</div>
    <div class="alert">Bottom start</div>
    <div class="alert">Bottom center</div>
    <div class="alert">Bottom end</div>
</div>

<x-lazy-toast top start />
<x-lazy-toast top center />
<x-lazy-toast top end />
<x-lazy-toast middle start />
<x-lazy-toast middle center />
<x-lazy-toast middle end />
<x-lazy-toast bottom start />
<x-lazy-toast bottom center />
<x-lazy-toast bottom end />
BLADE,
            ]],
            'tooltip' => [[
                'title' => 'Tooltip positions',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-tooltip tip="Top" position="top"><button class="btn">Top</button></x-lazy-tooltip>
    <x-lazy-tooltip tip="Bottom" position="bottom"><button class="btn">Bottom</button></x-lazy-tooltip>
    <x-lazy-tooltip tip="Left" position="left"><button class="btn">Left</button></x-lazy-tooltip>
    <x-lazy-tooltip tip="Right" position="right"><button class="btn">Right</button></x-lazy-tooltip>
    <x-lazy-tooltip tip="Open" open><button class="btn">Open</button></x-lazy-tooltip>
</div>
BLADE,
            ]],
            'theme-controller' => [[
                'title' => 'Theme controller inputs',
                'description' => 'ThemeController is an input. Add a visible daisyUI control class and label so users understand the interaction.',
                'code' => <<<'BLADE'
<div class="flex flex-wrap items-center gap-6">
    <label class="flex items-center gap-3">
        <span>Dark theme</span>
        <x-lazy-theme-controller theme="dark" type="checkbox" class="toggle" />
    </label>

    <label class="flex items-center gap-3">
        <span>Light theme</span>
        <x-lazy-theme-controller theme="light" type="radio" name="theme" class="radio" />
    </label>
</div>
BLADE,
            ]],
            'carousel' => [[
                'title' => 'Carousel layouts',
                'code' => <<<'BLADE'
<x-lazy-carousel>
    <x-lazy-carousel-item>Slide 1</x-lazy-carousel-item>
    <x-lazy-carousel-item>Slide 2</x-lazy-carousel-item>
</x-lazy-carousel>

<x-lazy-carousel vertical>
    <x-lazy-carousel-item>Slide 1</x-lazy-carousel-item>
    <x-lazy-carousel-item>Slide 2</x-lazy-carousel-item>
</x-lazy-carousel>
BLADE,
            ]],
            'footer' => [[
                'title' => 'Footer layouts',
                'code' => <<<'BLADE'
<x-lazy-footer>Default footer</x-lazy-footer>
<x-lazy-footer horizontal>Horizontal footer</x-lazy-footer>
<x-lazy-footer center>Centered footer</x-lazy-footer>
BLADE,
            ]],
            'stack' => [[
                'title' => 'Stack positions',
                'code' => <<<'BLADE'
<x-lazy-stack top>Top stack</x-lazy-stack>
<x-lazy-stack bottom>Bottom stack</x-lazy-stack>
<x-lazy-stack start>Start stack</x-lazy-stack>
<x-lazy-stack end>End stack</x-lazy-stack>
BLADE,
            ]],
            'skeleton' => [[
                'title' => 'Skeleton types',
                'code' => <<<'BLADE'
<x-lazy-skeleton class="h-24 w-24" />
<x-lazy-skeleton text class="h-4 w-48" />
BLADE,
            ]],
            'fab' => [[
                'title' => 'FAB actions',
                'description' => 'FAB becomes useful when it contains visible actions instead of an empty floating container.',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-8">
    <x-lazy-fab label="Actions">
        <button class="btn btn-circle">+</button>
        <button class="btn btn-circle">✎</button>
    </x-lazy-fab>

    <x-lazy-fab label="Flower" flower>
        <button class="btn btn-circle">★</button>
        <button class="btn btn-circle">♥</button>
    </x-lazy-fab>
</div>
BLADE,
            ]],
            'megamenu' => [[
                'title' => 'Megamenu modes',
                'code' => <<<'BLADE'
<x-lazy-megamenu>Default</x-lazy-megamenu>
<x-lazy-megamenu wide>Wide</x-lazy-megamenu>
<x-lazy-megamenu full>Full</x-lazy-megamenu>
<x-lazy-megamenu vertical>Vertical</x-lazy-megamenu>
BLADE,
            ]],
            'otp' => [[
                'title' => 'OTP modes',
                'code' => <<<'BLADE'
<x-lazy-otp name="otp" :length="6" />
<x-lazy-otp name="otp-joined" :length="6" joined />
<x-lazy-otp name="otp-text" :length="6" :numeric="false" />
<x-lazy-otp name="otp-readonly" value="123456" readonly />
BLADE,
            ]],
            'pagination' => [[
                'title' => 'Pagination modes',
                'code' => <<<'BLADE'
<x-lazy-pagination :current="5" :total="12" />
<x-lazy-pagination :current="5" :total="12" :show-edges="false" />
<x-lazy-pagination :current="5" :total="12" :window="1" />
BLADE,
            ]],
            'swap' => [[
                'title' => 'Swap modes',
                'code' => <<<'BLADE'
<x-lazy-swap on-label="ON" off-label="OFF" />
<x-lazy-swap on-label="ON" off-label="OFF" rotate />
<x-lazy-swap on-label="ON" off-label="OFF" flip />
<x-lazy-swap on-label="ON" off-label="OFF" active />
BLADE,
            ]],
            'button-group' => [[
                'title' => 'Button group',
                'code' => <<<'BLADE'
<x-lazy-btn-group>
    <x-lazy-btn>One</x-lazy-btn>
    <x-lazy-btn>Two</x-lazy-btn>
    <x-lazy-btn>Three</x-lazy-btn>
</x-lazy-btn-group>
BLADE,
            ]],
            'button-back' => [[
                'title' => 'Back button',
                'code' => <<<'BLADE'
<x-lazy-btn-back />
BLADE,
            ]],
            'button-delete' => [[
                'title' => 'Delete button',
                'code' => <<<'BLADE'
<x-lazy-btn-delete href="/posts/1" />
BLADE,
            ]],
            'button-logout' => [[
                'title' => 'Logout button',
                'code' => <<<'BLADE'
<x-lazy-btn-logout />
<x-lazy-btn-logout route="logout" />
BLADE,
            ]],
            'theme-switcher' => [[
                'title' => 'Theme switcher',
                'code' => <<<'BLADE'
<x-lazy-theme-switcher />
BLADE,
            ]],
            'avatar' => [[
                'title' => 'Avatar states',
                'code' => <<<'BLADE'
<div class="flex items-center gap-4">
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-orange.svg" alt="User" />
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-blue.svg" alt="Online" online-enabled />
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-purple.svg" alt="Offline" offline-enabled />
    <x-lazy-avatar class="w-12 rounded-full bg-neutral text-neutral-content" :src="null" placeholder-enabled>UI</x-lazy-avatar>
</div>
BLADE,
            ]],
            'avatar-group' => [[
                'title' => 'Avatar group spacing',
                'code' => <<<'BLADE'
<x-lazy-avatar-group spacing="6">
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-green.svg" />
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-orange.svg" />
    <x-lazy-avatar class="w-12 rounded-full" src="/images/docs/sample-blue.svg" />
</x-lazy-avatar-group>
BLADE,
            ]],
            'badge' => [[
                'title' => 'Badge content',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-2">
    <x-lazy-badge>Default</x-lazy-badge>
    <x-lazy-badge label="Label prop" />
    <x-lazy-badge outline>Outline</x-lazy-badge>
</div>
BLADE,
            ]],
            'carousel-item' => [[
                'title' => 'Carousel item',
                'code' => <<<'BLADE'
<x-lazy-carousel>
    <x-lazy-carousel-item class="w-full bg-base-200 p-8">First slide</x-lazy-carousel-item>
    <x-lazy-carousel-item class="w-full bg-base-300 p-8">Second slide</x-lazy-carousel-item>
</x-lazy-carousel>
BLADE,
            ]],
            'countdown' => [[
                'title' => 'Countdown values',
                'code' => <<<'BLADE'
<div class="flex gap-6">
    <x-lazy-countdown :value="59" label="Seconds" />
    <x-lazy-countdown :value="12" label="Minutes" />
    <x-lazy-countdown value="7" label="Days" />
</div>
BLADE,
            ]],
            'diff' => [[
                'title' => 'Before and after',
                'code' => <<<'BLADE'
<x-lazy-diff class="aspect-video">
    <x-slot:before>
        <div class="grid h-full place-content-center bg-primary text-primary-content">Before</div>
    </x-slot:before>
    <x-slot:after>
        <div class="grid h-full place-content-center bg-secondary text-secondary-content">After</div>
    </x-slot:after>
</x-lazy-diff>
BLADE,
            ]],
            'hover-3d' => [[
                'title' => 'Hover 3D zones',
                'code' => <<<'BLADE'
<x-lazy-hover-3d :zone-count="8" class="card w-64 bg-base-200 p-6 shadow">
    Hover this card
</x-lazy-hover-3d>
<x-lazy-hover-3d :zone-count="12" class="card w-64 bg-base-200 p-6 shadow">
    More hover zones
</x-lazy-hover-3d>
BLADE,
            ]],
            'hover-gallery' => [[
                'title' => 'Hover gallery images',
                'code' => <<<'BLADE'
<x-lazy-hover-gallery :images="[
    ['src' => '/images/docs/sample-purple.svg', 'alt' => 'Image 1'],
    ['src' => '/images/docs/sample-green.svg', 'alt' => 'Image 2'],
    ['src' => '/images/docs/sample-orange.svg', 'alt' => 'Image 3'],
]" />
BLADE,
            ]],
            'image' => [[
                'title' => 'Image usage',
                'code' => <<<'BLADE'
<x-lazy-image src="/images/docs/sample-blue.svg" alt="Landscape" class="rounded-box w-80" />
BLADE,
            ]],
            'kbd' => [[
                'title' => 'Keyboard sizes',
                'code' => <<<'BLADE'
<div class="flex items-center gap-2">
    <x-lazy-kbd value="Ctrl" xs />
    <x-lazy-kbd value="K" sm />
    <x-lazy-kbd value="Enter" md />
    <x-lazy-kbd value="Esc" lg />
    <x-lazy-kbd value="⌘" xl />
</div>
BLADE,
            ]],
            'list' => [[
                'title' => 'Structured list',
                'code' => <<<'BLADE'
<x-lazy-list>
    <x-lazy-list-row>First item</x-lazy-list-row>
    <x-lazy-list-row>Second item</x-lazy-list-row>
    <x-lazy-list-row>Third item</x-lazy-list-row>
</x-lazy-list>
BLADE,
            ]],
            'list-row' => [[
                'title' => 'List rows',
                'code' => <<<'BLADE'
<x-lazy-list>
    <x-lazy-list-row class="list-row">Default row</x-lazy-list-row>
    <x-lazy-list-row class="list-row font-semibold">Highlighted row</x-lazy-list-row>
</x-lazy-list>
BLADE,
            ]],
            'stats' => [[
                'title' => 'Stats layouts',
                'code' => <<<'BLADE'
<x-lazy-stats :items="[
    ['title' => 'Users', 'value' => 1200, 'description' => '+12%'],
    ['title' => 'Orders', 'value' => 320, 'description' => '+4%'],
]" />
<x-lazy-stats vertical :items="[
    ['title' => 'CPU', 'value' => '32%'],
    ['title' => 'RAM', 'value' => '61%'],
]" />
BLADE,
            ]],
            'stat' => [[
                'title' => 'Stat slots',
                'code' => <<<'BLADE'
<x-lazy-stat title="Downloads" value="31K" description="Jan 1st - Feb 1st" />
<x-lazy-stat title="Revenue" value="$8,400">
    <x-slot:figure>↗</x-slot:figure>
    <x-slot:actions><button class="btn btn-xs">Details</button></x-slot:actions>
</x-lazy-stat>
BLADE,
            ]],
            'status' => [[
                'title' => 'Status colors and sizes',
                'code' => <<<'BLADE'
<div class="flex items-center gap-4">
    <x-lazy-status neutral />
    <x-lazy-status primary />
    <x-lazy-status secondary />
    <x-lazy-status accent />
    <x-lazy-status info />
    <x-lazy-status success />
    <x-lazy-status warning />
    <x-lazy-status error />
</div>
BLADE,
            ]],
            'table' => [[
                'title' => 'Table data',
                'code' => <<<'BLADE'
<x-lazy-table
    :headers="[
        'name' => 'Name',
        'role' => 'Role',
        'status' => 'Status',
    ]"
    :rows="[
        ['name' => 'Alice', 'role' => 'Admin', 'status' => 'Active'],
        ['name' => 'Bob', 'role' => 'Editor', 'status' => 'Pending'],
    ]"
/>
BLADE,
            ], [
                'title' => 'Table styles',
                'code' => <<<'BLADE'
<x-lazy-table zebra :headers="['Name', 'Role']" :rows="[['Alice', 'Admin'], ['Bob', 'Editor']]" />
<x-lazy-table pin-rows :headers="['Name', 'Role']" :rows="[['Alice', 'Admin'], ['Bob', 'Editor']]" />
<x-lazy-table pin-cols :headers="['Name', 'Role']" :rows="[['Alice', 'Admin'], ['Bob', 'Editor']]" />
BLADE,
            ]],
            'text-rotate' => [[
                'title' => 'Rotating text',
                'code' => <<<'BLADE'
<x-lazy-text-rotate :items="['Fast', 'Reusable', 'Universal']" />
<x-lazy-text-rotate :items="['One', 'Two', 'Three']" :duration="1200" />
BLADE,
            ]],
            'timeline-item' => [[
                'title' => 'Timeline item states',
                'code' => <<<'BLADE'
<x-lazy-timeline>
    <x-lazy-timeline-item first>First</x-lazy-timeline-item>
    <x-lazy-timeline-item box>Middle</x-lazy-timeline-item>
    <x-lazy-timeline-item last>Last</x-lazy-timeline-item>
</x-lazy-timeline>
BLADE,
            ]],
            'breadcrumbs' => [[
                'title' => 'Breadcrumb item formats',
                'code' => <<<'BLADE'
<x-lazy-breadcrumbs :items="[
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Components', 'href' => '/docs/components'],
    ['label' => 'Button', 'current' => true],
]" />
BLADE,
            ]],
            'dock' => [[
                'title' => 'Dock with items',
                'description' => 'Dock is normally fixed to the bottom of the viewport. In the documentation preview it is sandboxed so all items remain visible.',
                'code' => <<<'BLADE'
<x-lazy-dock :items="[
    ['label' => 'Home', 'content' => '⌂', 'active' => true],
    ['label' => 'Search', 'content' => '⌕'],
    ['label' => 'Profile', 'content' => '●'],
]" />
BLADE,
            ]],
            'dock-item' => [[
                'title' => 'Dock item states',
                'code' => <<<'BLADE'
<x-lazy-dock>
    <x-lazy-dock-item label="Home" active>⌂</x-lazy-dock-item>
    <x-lazy-dock-item label="Search">⌕</x-lazy-dock-item>
    <x-lazy-dock-item label="Profile">●</x-lazy-dock-item>
</x-lazy-dock>
BLADE,
            ]],
            'link' => [[
                'title' => 'Link styles',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-link href="/" primary>Primary</x-lazy-link>
    <x-lazy-link href="/" secondary>Secondary</x-lazy-link>
    <x-lazy-link href="/" accent>Accent</x-lazy-link>
    <x-lazy-link href="/" hover>Hover</x-lazy-link>
</div>
BLADE,
            ]],
            'menu' => [[
                'title' => 'Menu item states',
                'code' => <<<'BLADE'
<x-lazy-menu-list>
    <x-lazy-menu label="Default" href="#" />
    <x-lazy-menu label="Active" href="#" active />
    <x-lazy-menu label="Disabled" href="#" disabled />
    <x-lazy-menu label="Focused" href="#" focus />
    <x-lazy-menu label="Count" href="#" :count="12" />
    <x-lazy-menu label="99+" href="#" :count="120" />
    <x-lazy-menu label="Toggle" href="#" toggle />
</x-lazy-menu-list>
BLADE,
            ]],
            'menu-list' => [[
                'title' => 'Menu layouts',
                'code' => <<<'BLADE'
<x-lazy-menu-list>
    <x-lazy-menu label="One" href="#" />
    <x-lazy-menu label="Two" href="#" />
</x-lazy-menu-list>

<x-lazy-menu-list horizontal>
    <x-lazy-menu label="One" href="#" />
    <x-lazy-menu label="Two" href="#" />
</x-lazy-menu-list>

<x-lazy-menu-list paged>
    <x-lazy-menu label="Page 1" href="#" />
    <x-lazy-menu label="Page 2" href="#" />
</x-lazy-menu-list>
BLADE,
            ]],
            'navbar' => [[
                'title' => 'Navbar slots',
                'code' => <<<'BLADE'
<x-lazy-navbar class="bg-base-200 rounded-box px-4">
    <x-slot:start><strong>Brand</strong></x-slot:start>
    <x-slot:center>Center navigation</x-slot:center>
    <x-slot:end><button class="btn btn-sm">Login</button></x-slot:end>
</x-lazy-navbar>
BLADE,
            ]],
            'pagination-item' => [[
                'title' => 'Pagination item states',
                'code' => <<<'BLADE'
<div class="join">
    <x-lazy-pagination-item href="?page=1">1</x-lazy-pagination-item>
    <x-lazy-pagination-item href="?page=2" active>2</x-lazy-pagination-item>
    <x-lazy-pagination-item disabled>3</x-lazy-pagination-item>
</div>
BLADE,
            ]],
            'step' => [[
                'title' => 'Step colors',
                'code' => <<<'BLADE'
<x-lazy-steps>
    <x-lazy-step primary>Primary</x-lazy-step>
    <x-lazy-step secondary>Secondary</x-lazy-step>
    <x-lazy-step accent>Accent</x-lazy-step>
    <x-lazy-step success>Success</x-lazy-step>
</x-lazy-steps>
BLADE,
            ]],
            'tab' => [[
                'title' => 'Tab states',
                'code' => <<<'BLADE'
<x-lazy-tabs type="box">
    <x-lazy-tab label="Active" active />
    <x-lazy-tab label="Default" />
    <x-lazy-tab label="Disabled" disabled />
</x-lazy-tabs>
BLADE,
            ]],
            'alert' => [[
                'title' => 'Alert types',
                'code' => <<<'BLADE'
<div class="space-y-3">
    <x-lazy-alert info message="Information" />
    <x-lazy-alert success message="Saved successfully" />
    <x-lazy-alert warning message="Check this value" />
    <x-lazy-alert error message="Something went wrong" />
</div>
BLADE,
            ], [
                'title' => 'Alert with actions',
                'code' => <<<'BLADE'
<x-lazy-alert warning message="Your session will expire soon">
    <x-slot:actions>
        <button class="btn btn-sm">Extend</button>
    </x-slot:actions>
</x-lazy-alert>
BLADE,
            ]],
            'progress' => [[
                'title' => 'Progress values',
                'code' => <<<'BLADE'
<div class="space-y-3">
    <x-lazy-progress primary :value="20" :max="100" />
    <x-lazy-progress success :value="55" :max="100" />
    <x-lazy-progress warning :value="80" :max="100" />
    <x-lazy-progress error :value="100" :max="100" />
    <x-lazy-progress />
</div>
BLADE,
            ]],
            'radial' => [[
                'title' => 'Radial progress',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-radial :value="25" />
    <x-lazy-radial :value="50" label="Half" />
    <x-lazy-radial :value="75" size="8rem" thickness="8px" primary />
</div>
BLADE,
            ]],
            'error' => [[
                'title' => 'Error messages',
                'code' => <<<'BLADE'
<x-lazy-error message="This field is required." />
<x-lazy-error>Custom validation error</x-lazy-error>
BLADE,
            ]],
            'checkbox' => [[
                'title' => 'Checkbox states',
                'code' => <<<'BLADE'
<div class="space-y-2">
    <x-lazy-checkbox name="terms" label="Accept terms" />
    <x-lazy-checkbox name="checked" label="Checked" checked />
    <x-lazy-checkbox name="disabled" label="Disabled" disabled />
</div>
BLADE,
            ]],
            'choices' => [[
                'title' => 'Choices options',
                'code' => <<<'BLADE'
<x-lazy-choices
    name="role"
    label="Role"
    placeholder="Choose a role"
    :options="[
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => ['label' => 'Viewer', 'disabled' => true],
    ]"
/>
BLADE,
            ], [
                'title' => 'Choices with Livewire',
                'code' => <<<'BLADE'
<x-lazy-choices
    wire:model.live="role"
    label="Role"
    :options="$roles"
/>
BLADE,
                'render' => false,
            ]],
            'fieldset' => [[
                'title' => 'Fieldset with hint',
                'code' => <<<'BLADE'
<x-lazy-fieldset legend="Profile" label="Personal information" hint="All fields are optional">
    <x-lazy-input name="name" placeholder="Name" />
</x-lazy-fieldset>
BLADE,
            ]],
            'file-input' => [[
                'title' => 'File input styles',
                'code' => <<<'BLADE'
<div class="space-y-3">
    <x-lazy-file-input name="file" />
    <x-lazy-file-input name="ghost-file" ghost />
    <x-lazy-file-input name="image" accept="image/*" primary />
</div>
BLADE,
            ]],
            'filter' => [[
                'title' => 'Filter options',
                'code' => <<<'BLADE'
<x-lazy-filter
    name="status"
    value="active"
    :options="[
        'all' => 'All',
        'active' => 'Active',
        'disabled' => 'Disabled',
    ]"
/>
BLADE,
            ]],
            'input' => [[
                'title' => 'Input states',
                'code' => <<<'BLADE'
<div class="space-y-3">
    <x-lazy-input name="name" label="Name" placeholder="John" />
    <x-lazy-input name="email" label="Email" type="email" required />
    <x-lazy-input name="search" placeholder="Search" validator hint="Enter at least 3 characters" />
    <x-lazy-input name="disabled" value="Disabled" disabled />
</div>
BLADE,
            ]],
            'input-group' => [[
                'title' => 'Input group',
                'code' => <<<'BLADE'
<x-lazy-input-group>
    <span class="btn join-item">@</span>
    <x-lazy-input name="username" class="join-item" placeholder="username" />
</x-lazy-input-group>
BLADE,
            ]],
            'label' => [[
                'title' => 'Label states',
                'code' => <<<'BLADE'
<x-lazy-label for="email" label="Email" />
<x-lazy-label for="name" label="Name" required />
<x-lazy-label for="password" label="Password" has-error />
<x-lazy-label for="bio" label="Biography" hr />
BLADE,
            ]],
            'radio' => [[
                'title' => 'Radio states',
                'code' => <<<'BLADE'
<div class="flex gap-4">
    <x-lazy-radio name="plan" value="free" checked />
    <x-lazy-radio name="plan" value="pro" primary />
    <x-lazy-radio name="plan" value="team" disabled />
</div>
BLADE,
            ]],
            'richtext' => [[
                'title' => 'Rich text editor',
                'code' => <<<'BLADE'
<x-lazy-richtext
    wire:model="body"
    placeholder="Write your article..."
/>
BLADE,
                'render' => false,
            ], [
                'title' => 'Rich text required',
                'code' => <<<'BLADE'
<x-lazy-richtext
    wire:model.live="body"
    placeholder="Required content"
    required
/>
BLADE,
                'render' => false,
            ]],
            'select' => [[
                'title' => 'Select options',
                'code' => <<<'BLADE'
<x-lazy-select
    name="role"
    label="Role"
    placeholder="Select role"
    :options="[
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => ['label' => 'Viewer', 'disabled' => true],
    ]"
/>
BLADE,
            ], [
                'title' => 'Select modes',
                'code' => <<<'BLADE'
<x-lazy-select name="required" label="Required" :options="['a' => 'A', 'b' => 'B']" required />
<x-lazy-select name="multiple" label="Multiple" :options="['a' => 'A', 'b' => 'B']" multiple />
<x-lazy-select name="validator" label="Validator" :options="['a' => 'A', 'b' => 'B']" validator hint="Pick one" />
<x-lazy-select name="ghost" :options="['a' => 'A', 'b' => 'B']" color="ghost" />
BLADE,
            ]],
            'textarea' => [[
                'title' => 'Textarea states',
                'code' => <<<'BLADE'
<x-lazy-textarea name="bio" placeholder="Biography" />
<x-lazy-textarea name="required-bio" placeholder="Required" required />
<x-lazy-textarea name="validated-bio" validator hint="Minimum 10 characters" />
<x-lazy-textarea name="disabled-bio" value="Disabled text" disabled />
BLADE,
            ]],
            'toggle' => [[
                'title' => 'Toggle states',
                'code' => <<<'BLADE'
<div class="flex gap-4">
    <x-lazy-toggle name="enabled" />
    <x-lazy-toggle name="active" checked />
    <x-lazy-toggle name="disabled" disabled />
    <x-lazy-toggle name="primary-toggle" primary />
</div>
BLADE,
            ]],
            'form' => [[
                'title' => 'Standard form',
                'code' => <<<'BLADE'
<x-lazy-form action="/profile" method="POST">
    <x-lazy-form-input name="name" label="Name" />
    <x-lazy-btn type="submit" primary>Save</x-lazy-btn>
</x-lazy-form>
BLADE,
            ], [
                'title' => 'Spoofed HTTP methods',
                'code' => <<<'BLADE'
<x-lazy-form action="/posts/1" method="PATCH">
    <x-lazy-form-input name="title" label="Title" />
    <x-lazy-btn type="submit" primary>Update</x-lazy-btn>
</x-lazy-form>
BLADE,
            ], [
                'title' => 'Livewire form',
                'code' => <<<'BLADE'
<x-lazy-form wire:submit="save">
    <x-lazy-form-input wire:model="name" label="Name" />
    <x-lazy-btn type="submit" primary>Save</x-lazy-btn>
</x-lazy-form>
BLADE,
                'render' => false,
            ]],
            'form-group' => [[
                'title' => 'Form group states',
                'code' => <<<'BLADE'
<x-lazy-form-group label="Email">
    <x-lazy-input name="email" type="email" />
</x-lazy-form-group>

<x-lazy-form-group label="Password" help="At least 8 characters" hr>
    <x-lazy-input name="password" type="password" />
</x-lazy-form-group>
BLADE,
            ]],
            'form-input' => [[
                'title' => 'Form input options',
                'code' => <<<'BLADE'
<x-lazy-form-input name="name" label="Name" />
<x-lazy-form-input name="email" label="Email" type="email" required />
<x-lazy-form-input name="username" label="Username" help="Public profile name" />
<x-lazy-form-input name="slug" label="Slug" outer-class="max-w-md" hr />
BLADE,
            ]],
            'form-select' => [[
                'title' => 'Form select',
                'code' => <<<'BLADE'
<x-lazy-form-select
    name="role"
    label="Role"
    :options="['admin' => 'Admin', 'editor' => 'Editor']"
/>
<x-lazy-form-select
    name="required-role"
    label="Required role"
    :options="['admin' => 'Admin', 'editor' => 'Editor']"
    required
    help="Choose access level"
/>
BLADE,
            ]],
            'form-textarea' => [[
                'title' => 'Form textarea',
                'code' => <<<'BLADE'
<x-lazy-form-textarea name="bio" label="Biography" />
<x-lazy-form-textarea name="notes" label="Notes" help="Internal only" hr />
<x-lazy-form-textarea name="required-notes" label="Required notes" required />
BLADE,
            ]],
            'form-checkbox' => [[
                'title' => 'Form checkbox',
                'code' => <<<'BLADE'
<x-lazy-form-checkbox name="terms" label="Accept terms" />
<x-lazy-form-checkbox name="newsletter" label="Newsletter" help="Receive product updates" checked />
<x-lazy-form-checkbox name="archived" label="Archived" hr />
BLADE,
            ]],
            'form-toggle' => [[
                'title' => 'Form toggle',
                'code' => <<<'BLADE'
<x-lazy-form-toggle name="enabled" label="Enabled" />
<x-lazy-form-toggle name="notifications" label="Notifications" help="Send email notifications" checked />
<x-lazy-form-toggle name="archived" label="Archived" hr />
BLADE,
            ]],
            'form-image' => [[
                'title' => 'Form image',
                'code' => <<<'BLADE'
<x-lazy-form-image name="photo" label="Photo" />
<x-lazy-form-image name="avatar" label="Avatar" required help="PNG or JPG" />
<x-lazy-form-image name="cover" label="Cover" src="/images/docs/sample-purple.svg" outer-class="max-w-lg" />
BLADE,
            ]],
            'form-richtext' => [[
                'title' => 'Form rich text',
                'code' => <<<'BLADE'
<x-lazy-form-richtext wire:model="body" label="Body" />
<x-lazy-form-richtext wire:model="summary" label="Summary" help="Shown on cards" />
<x-lazy-form-richtext wire:model="content" label="Content" required hr />
BLADE,
                'render' => false,
            ]],
            'drawer' => [[
                'title' => 'Drawer sides',
                'code' => <<<'BLADE'
<x-lazy-drawer id="left-drawer">
    <button class="btn" onclick="document.getElementById('left-drawer').click()">Open</button>
    <x-slot:side>
        <ul class="menu">
            <li><a>Home</a></li>
            <li><a>Settings</a></li>
        </ul>
    </x-slot:side>
</x-lazy-drawer>

<x-lazy-drawer id="right-drawer" end>
    Right drawer content
    <x-slot:side>Right side</x-slot:side>
</x-lazy-drawer>
BLADE,
            ], [
                'title' => 'Drawer sizing',
                'code' => <<<'BLADE'
<x-lazy-drawer width="xs" padding="xs" background="base-200">XS drawer</x-lazy-drawer>
<x-lazy-drawer width="md" padding="md" background="base-100">MD drawer</x-lazy-drawer>
<x-lazy-drawer width="lg" padding="lg" background="neutral">LG drawer</x-lazy-drawer>
<x-lazy-drawer width="full" padding="none" background="primary">Full drawer</x-lazy-drawer>
BLADE,
            ]],
            'mockup-browser' => [[
                'title' => 'Browser mockup',
                'code' => <<<'BLADE'
<x-lazy-mockup-browser url="https://step2.dev" class="border border-base-300">
    <div class="grid h-48 place-content-center bg-base-200">Page content</div>
</x-lazy-mockup-browser>
BLADE,
            ]],
            'mockup-code' => [[
                'title' => 'Code mockup',
                'code' => <<<'BLADE'
<x-lazy-mockup-code prefix="$">composer require step2dev/lazy-ui</x-lazy-mockup-code>
BLADE,
            ], [
                'title' => 'Code mockup lines',
                'code' => <<<'BLADE'
<x-lazy-mockup-code :lines="[
    ['prefix' => '$', 'code' => 'composer install'],
    ['prefix' => '>', 'code' => 'Installing dependencies...', 'class' => 'text-warning'],
    ['prefix' => '✓', 'code' => 'Done', 'class' => 'text-success'],
]" />
BLADE,
            ]],
            'mockup-phone' => [[
                'title' => 'Phone mockup',
                'code' => <<<'BLADE'
<x-lazy-mockup-phone>
    <div class="grid h-full place-content-center bg-base-200">Mobile app</div>
</x-lazy-mockup-phone>
BLADE,
            ]],
            'mockup-window' => [[
                'title' => 'Window mockup',
                'code' => <<<'BLADE'
<x-lazy-mockup-window class="border border-base-300">
    <div class="grid h-48 place-content-center bg-base-200">Desktop app</div>
</x-lazy-mockup-window>
BLADE,
            ]],
            'country-time-widget' => [[
                'title' => 'Country time widget',
                'code' => <<<'BLADE'
<x-lazy-country-time-widget />
BLADE,
            ]],
            default => [],
        };
    }

    private function exampleTag(string $tag, array $attributes = [], ?string $label = null): string
    {
        $parts = collect($attributes)
            ->map(function (mixed $value, string $key): string {
                if ($value === true) {
                    return $key;
                }

                if ($value === false || $value === null) {
                    return '';
                }

                return $key.'="'.e((string) $value).'"';
            })
            ->filter()
            ->implode(' ');

        $parts = $parts !== '' ? ' '.$parts : '';
        $label ??= 'Example';

        return match ($tag) {
            'btn' => '<x-lazy-btn'.$parts.'>'.e($label).'</x-lazy-btn>',
            'badge' => '<x-lazy-badge'.$parts.'>'.e($label).'</x-lazy-badge>',
            'loading' => '<span class="inline-flex items-center gap-2"><x-lazy-loading'.$parts.' /><span>'.e($label).'</span></span>',
            'kbd' => '<span class="inline-flex items-center gap-2"><x-lazy-kbd'.$parts.' value="'.e($label).'" /><span>'.e($label).'</span></span>',
            'checkbox' => '<label class="inline-flex items-center gap-2"><x-lazy-checkbox'.$parts.' name="checkbox-'.str($label)->slug().'" /><span>'.e($label).'</span></label>',
            'radio' => '<label class="inline-flex items-center gap-2"><x-lazy-radio'.$parts.' name="radio-example" value="'.str($label)->slug().'" /><span>'.e($label).'</span></label>',
            'toggle' => '<label class="inline-flex items-center gap-2"><x-lazy-toggle'.$parts.' name="toggle-'.str($label)->slug().'" /><span>'.e($label).'</span></label>',
            'status' => '<span class="inline-flex items-center gap-2"><x-lazy-status'.$parts.' /><span>'.e($label).'</span></span>',
            'file-input' => '<label class="grid min-w-64 gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-file-input'.$parts.' name="file-'.str($label)->slug().'" /></label>',
            'otp' => '<label class="grid gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-otp'.$parts.' name="otp-'.str($label)->slug().'" value="123456" /></label>',
            'range' => '<label class="grid w-64 gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-range'.$parts.' min="0" max="100" value="55" /></label>',
            'rating' => '<div class="inline-flex items-center gap-3"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-rating'.$parts.' name="rating-'.str($label)->slug().'" :value="3" /></div>',
            'select' => '<label class="grid min-w-56 gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-select'.$parts.' name="select-'.str($label)->slug().'" :options="[\'one\' => \'Option one\', \'two\' => \'Option two\']" /></label>',
            'textarea' => '<label class="grid min-w-64 gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-textarea'.$parts.' name="textarea-'.str($label)->slug().'" placeholder="'.e($label).'" /></label>',
            'input' => '<label class="grid min-w-56 gap-2"><span class="text-sm font-medium">'.e($label).'</span><x-lazy-input'.$parts.' name="input-'.str($label)->slug().'" placeholder="'.e($label).'" /></label>',
            'link' => '<x-lazy-link'.$parts.' href="#">'.e($label).'</x-lazy-link>',
            'step' => '<x-lazy-steps><x-lazy-step'.$parts.'>'.e($label).'</x-lazy-step><x-lazy-step>Next</x-lazy-step></x-lazy-steps>',
            'indicator' => '<x-lazy-indicator'.$parts.' indicator="3"><button class="btn">'.e($label).'</button></x-lazy-indicator>',
            'tabs' => '<x-lazy-tabs'.$parts.' :items="[[\'label\' => \'Overview\', \'content\' => \'Overview content\', \'active\' => true], [\'label\' => \'Details\', \'content\' => \'Details content\']]" />',
            'menu-list' => '<x-lazy-menu-list'.$parts.'><x-lazy-menu label="'.e($label).'" href="#" /><x-lazy-menu label="Second item" href="#" /></x-lazy-menu-list>',
            'dock' => '<x-lazy-dock'.$parts.' :items="[[\'label\' => \'Home\', \'content\' => \'⌂\', \'active\' => true], [\'label\' => \'Search\', \'content\' => \'⌕\'], [\'label\' => \'Profile\', \'content\' => \'●\']]" />',
            'megamenu' => '<x-lazy-megamenu'.$parts.'><div class="grid grid-cols-2 gap-3 p-4"><a class="link" href="#">'.e($label).'</a><a class="link" href="#">Docs</a><a class="link" href="#">Blog</a><a class="link" href="#">About</a></div></x-lazy-megamenu>',
            'aura' => '<x-lazy-aura'.$parts.'><div class="card w-56 bg-base-100 shadow"><div class="card-body p-5"><strong>'.e($label).'</strong><span class="text-sm opacity-70">Aura preview</span></div></div></x-lazy-aura>',
            default => '<x-lazy-'.$tag.$parts.'>'.e($label).'</x-lazy-'.$tag.'>',
        };
    }

    private function resolveComponentClass(string $tag): ?string
    {
        $aliases = Blade::getClassComponentAliases();

        return $aliases['lazy-'.$tag] ?? null;
    }

    private function resolveParameters(?string $class): array
    {
        if ($class === null || ! class_exists($class)) {
            return [];
        }

        $constructor = (new ReflectionClass($class))->getConstructor();

        if ($constructor === null) {
            return [];
        }

        return collect($constructor->getParameters())
            ->map(fn (ReflectionParameter $parameter): array => [
                'name' => $parameter->getName(),
                'type' => $this->formatType($parameter),
                'required' => ! $parameter->isOptional(),
                'default' => $this->formatDefault($parameter),
            ])
            ->values()
            ->all();
    }

    private function formatType(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() && $type->getName() !== 'mixed' ? '?' : '').$type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            return collect($type->getTypes())
                ->map(fn (ReflectionNamedType $type): string => $type->getName())
                ->implode('|');
        }

        return 'mixed';
    }

    private function formatDefault(ReflectionParameter $parameter): string
    {
        if (! $parameter->isDefaultValueAvailable()) {
            return '—';
        }

        $value = $parameter->getDefaultValue();

        return match (true) {
            $value === null => 'null',
            $value === true => 'true',
            $value === false => 'false',
            is_string($value) => "'".$value."'",
            is_array($value) => $value === [] ? '[]' : json_encode($value, JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }
}
