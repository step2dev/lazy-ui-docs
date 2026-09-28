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

        if ($parameterNames->contains('color')) {
            $examples[] = [
                'title' => 'Colors',
                'code' => collect(['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'])
                    ->map(fn (string $color): string => $this->exampleTag($tag, [$color => true], ucfirst($color)))
                    ->implode("\n"),
            ];
        }

        if ($parameterNames->contains('size')) {
            $examples[] = [
                'title' => 'Sizes',
                'code' => collect(['xs', 'sm', 'md', 'lg', 'xl'])
                    ->map(fn (string $size): string => $this->exampleTag($tag, [$size => true], strtoupper($size)))
                    ->implode("\n"),
            ];
        }

        $layoutFlags = $parameterNames
            ->intersect(['vertical', 'horizontal', 'top', 'middle', 'bottom', 'start', 'center', 'end', 'left', 'right'])
            ->values();

        if ($layoutFlags->isNotEmpty()) {
            $examples[] = [
                'title' => 'Positions and layout',
                'code' => $layoutFlags
                    ->map(fn (string $flag): string => $this->exampleTag($tag, [$flag => true], ucfirst($flag)))
                    ->implode("\n"),
            ];
        }

        foreach ($this->exhaustiveCommonExamples($slug, $tag, $parameterNames) as $example) {
            $examples[] = $example;
        }

        foreach ($this->componentSpecificExamples($slug) as $example) {
            $examples[] = $example;
        }

        return $examples;
    }

    private function exhaustiveCommonExamples(string $slug, string $tag, \Illuminate\Support\Collection $parameterNames): array
    {
        $examples = [];

        $forcedColors = [
            'button' => ['neutral', 'primary', 'secondary', 'accent', 'ghost', 'info', 'success', 'warning', 'error', 'danger', 'link'],
            'badge' => ['neutral', 'primary', 'secondary', 'accent', 'ghost', 'info', 'success', 'warning', 'error', 'danger'],
            'alert' => ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error', 'danger'],
            'link' => ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'],
            'chat' => ['primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'],
            'tooltip' => ['primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'],
            'input' => ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error', 'ghost', 'no-border'],
            'select' => ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error', 'ghost', 'no-border'],
            'textarea' => ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error', 'ghost', 'no-border'],
        ];

        $forcedSizes = [
            'button' => ['xs', 'sm', 'md', 'lg', 'xl'],
            'badge' => ['xs', 'sm', 'md', 'lg', 'xl'],
        ];

        if (isset($forcedColors[$slug]) && ! $parameterNames->contains('color')) {
            $examples[] = [
                'title' => 'Colors',
                'code' => collect($forcedColors[$slug])
                    ->map(fn (string $color): string => $this->exampleTag($tag, [$color => true], ucfirst($color)))
                    ->implode("\n"),
            ];
        }

        if (isset($forcedSizes[$slug]) && ! $parameterNames->contains('size')) {
            $examples[] = [
                'title' => 'Sizes',
                'code' => collect($forcedSizes[$slug])
                    ->map(fn (string $size): string => $this->exampleTag($tag, [$size => true], strtoupper($size)))
                    ->implode("\n"),
            ];
        }

        $enumOptions = $this->enumOptions($slug);

        foreach ($enumOptions as $parameter => $values) {
            $examples[] = [
                'title' => str($parameter)->headline().' values',
                'code' => collect($values)
                    ->map(fn (string|int|float $value): string => $this->exampleTag(
                        $tag,
                        [$parameter => $value],
                        ucfirst(str_replace(['-', '_'], ' ', (string) $value))
                    ))
                    ->implode("\n"),
            ];
        }

        $booleanParameters = collect($this->parameters)
            ->filter(fn (array $parameter): bool => str_contains($parameter['type'], 'bool'))
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
            'aura' => [
                'type' => ['dual', 'rainbow', 'holo', 'gold', 'silver'],
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
            'mask' => [
                'shape' => [
                    'squircle', 'heart', 'hexagon', 'hexagon-2', 'decagon', 'pentagon', 'diamond', 'circle',
                    'star', 'star-2', 'triangle', 'triangle-2', 'triangle-3', 'triangle-4', 'square',
                    'parallelogram', 'parallelogram-2', 'parallelogram-3', 'parallelogram-4',
                ],
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
                'title' => 'Aura types',
                'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-aura type="dual">Dual</x-lazy-aura>
    <x-lazy-aura type="rainbow">Rainbow</x-lazy-aura>
    <x-lazy-aura type="holo">Holo</x-lazy-aura>
    <x-lazy-aura type="gold">Gold</x-lazy-aura>
    <x-lazy-aura type="silver">Silver</x-lazy-aura>
    <x-lazy-aura type="rainbow" glow>Glow</x-lazy-aura>
</div>
BLADE,
            ]],
            'calendar' => [[
                'title' => 'Calendar drivers',
                'code' => <<<'BLADE'
<x-lazy-calendar driver="native" />
<x-lazy-calendar driver="cally" />
<x-lazy-calendar driver="vc" />
<x-lazy-calendar driver="react-day-picker" />
BLADE,
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
                'code' => <<<'BLADE'
<x-lazy-indicator indicator="1" horizontal="start" vertical="top"><button class="btn">Start top</button></x-lazy-indicator>
<x-lazy-indicator indicator="2" horizontal="center" vertical="middle"><button class="btn">Center middle</button></x-lazy-indicator>
<x-lazy-indicator indicator="3" horizontal="end" vertical="bottom"><button class="btn">End bottom</button></x-lazy-indicator>
BLADE,
            ]],
            'join' => [[
                'title' => 'Join directions',
                'code' => <<<'BLADE'
<x-lazy-join horizontal>
    <x-lazy-btn>One</x-lazy-btn>
    <x-lazy-btn>Two</x-lazy-btn>
</x-lazy-join>

<x-lazy-join vertical>
    <x-lazy-btn>One</x-lazy-btn>
    <x-lazy-btn>Two</x-lazy-btn>
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
                'title' => 'Dropdown modes',
                'code' => <<<'BLADE'
<x-lazy-dropdown label="Click" />
<x-lazy-dropdown label="Hover" hover />
<x-lazy-dropdown label="Open" open />
<x-lazy-dropdown label="Top" top />
<x-lazy-dropdown label="Bottom end" bottom end />
BLADE,
            ]],
            'modal' => [[
                'title' => 'Modal positions',
                'code' => <<<'BLADE'
<x-lazy-modal id="modal-top" top>Top modal</x-lazy-modal>
<x-lazy-modal id="modal-middle" middle>Middle modal</x-lazy-modal>
<x-lazy-modal id="modal-bottom" bottom>Bottom modal</x-lazy-modal>
<x-lazy-modal id="modal-start" start>Start modal</x-lazy-modal>
<x-lazy-modal id="modal-end" end>End modal</x-lazy-modal>
BLADE,
            ]],
            'mask' => [[
                'title' => 'Mask shapes',
                'code' => <<<'BLADE'
<div class="grid grid-cols-3 gap-4 md:grid-cols-5">
    <x-lazy-mask shape="squircle"><img src="https://picsum.photos/100" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="heart"><img src="https://picsum.photos/101" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="hexagon"><img src="https://picsum.photos/102" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="hexagon-2"><img src="https://picsum.photos/103" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="decagon"><img src="https://picsum.photos/104" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="pentagon"><img src="https://picsum.photos/105" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="diamond"><img src="https://picsum.photos/106" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="circle"><img src="https://picsum.photos/107" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="star"><img src="https://picsum.photos/108" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="star-2"><img src="https://picsum.photos/109" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="triangle"><img src="https://picsum.photos/110" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="triangle-2"><img src="https://picsum.photos/111" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="triangle-3"><img src="https://picsum.photos/112" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="triangle-4"><img src="https://picsum.photos/113" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="square"><img src="https://picsum.photos/114" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="parallelogram"><img src="https://picsum.photos/115" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="parallelogram-2"><img src="https://picsum.photos/116" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="parallelogram-3"><img src="https://picsum.photos/117" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="parallelogram-4"><img src="https://picsum.photos/118" alt="" /></x-lazy-mask>
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
                'title' => 'Toast positions',
                'code' => <<<'BLADE'
<x-lazy-toast top start />
<x-lazy-toast top center />
<x-lazy-toast top end />
<x-lazy-toast middle center />
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
                'title' => 'Controller types',
                'code' => <<<'BLADE'
<x-lazy-theme-controller theme="light" type="checkbox" />
<x-lazy-theme-controller theme="dark" type="radio" />
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
                'title' => 'FAB modes',
                'code' => <<<'BLADE'
<x-lazy-fab label="Menu" />
<x-lazy-fab label="Flower" flower />
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
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?1" alt="User" />
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?2" alt="Online" online-enabled />
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?3" alt="Offline" offline-enabled />
    <x-lazy-avatar class="w-12 rounded-full bg-neutral text-neutral-content" :src="null" placeholder-enabled>UI</x-lazy-avatar>
</div>
BLADE,
            ]],
            'avatar-group' => [[
                'title' => 'Avatar group spacing',
                'code' => <<<'BLADE'
<x-lazy-avatar-group spacing="6">
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?11" />
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?12" />
    <x-lazy-avatar class="w-12 rounded-full" src="https://picsum.photos/100/100?13" />
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
    ['src' => 'https://picsum.photos/400/300?1', 'alt' => 'Image 1'],
    ['src' => 'https://picsum.photos/400/300?2', 'alt' => 'Image 2'],
    ['src' => 'https://picsum.photos/400/300?3', 'alt' => 'Image 3'],
]" />
BLADE,
            ]],
            'image' => [[
                'title' => 'Image usage',
                'code' => <<<'BLADE'
<x-lazy-image src="https://picsum.photos/640/360" alt="Landscape" class="rounded-box w-80" />
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
                'code' => <<<'BLADE'
<x-lazy-dock :items="[
    ['label' => 'Home', 'href' => '/', 'active' => true],
    ['label' => 'Search', 'href' => '/search'],
    ['label' => 'Profile', 'href' => '/profile'],
]" />
BLADE,
            ]],
            'dock-item' => [[
                'title' => 'Dock item states',
                'code' => <<<'BLADE'
<x-lazy-dock>
    <x-lazy-dock-item href="/" label="Home" active />
    <x-lazy-dock-item href="/search" label="Search" />
    <x-lazy-dock-item href="/profile" label="Profile" />
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
<x-lazy-form-image name="cover" label="Cover" src="/images/cover.jpg" outer-class="max-w-lg" />
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
    ['prefix' => '

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

        if ($label !== null && collect($this->parameters)->pluck('name')->contains('label')) {
            return '<x-lazy-'.$tag.$parts.' label="'.e($label).'" />';
        }

        return '<x-lazy-'.$tag.$parts.' />';
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
, 'code' => 'composer install'],
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

        if ($label !== null && collect($this->parameters)->pluck('name')->contains('label')) {
            return '<x-lazy-'.$tag.$parts.' label="'.e($label).'" />';
        }

        return '<x-lazy-'.$tag.$parts.' />';
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
