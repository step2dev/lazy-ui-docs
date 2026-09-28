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

        foreach ($this->componentSpecificExamples($slug) as $example) {
            $examples[] = $example;
        }

        return $examples;
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
<div class="flex flex-wrap gap-4">
    <x-lazy-mask shape="squircle"><img src="https://picsum.photos/100" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="heart"><img src="https://picsum.photos/101" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="hexagon"><img src="https://picsum.photos/102" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="triangle"><img src="https://picsum.photos/103" alt="" /></x-lazy-mask>
    <x-lazy-mask shape="circle"><img src="https://picsum.photos/104" alt="" /></x-lazy-mask>
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
