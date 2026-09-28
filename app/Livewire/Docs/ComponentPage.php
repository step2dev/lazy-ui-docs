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
        return match ($slug) {
            'loading' => [
                [
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
                ],
                [
                    'title' => 'Loading sizes',
                    'code' => <<<'BLADE'
<div class="flex items-center gap-4">
    <x-lazy-loading xs />
    <x-lazy-loading sm />
    <x-lazy-loading md />
    <x-lazy-loading lg />
</div>
BLADE,
                ],
                [
                    'title' => 'Loading colors',
                    'code' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-lazy-loading primary />
    <x-lazy-loading secondary />
    <x-lazy-loading accent />
    <x-lazy-loading info />
    <x-lazy-loading success />
    <x-lazy-loading warning />
    <x-lazy-loading error />
</div>
BLADE,
                ],
            ],
            'button' => [
                [
                    'title' => 'Button variants',
                    'code' => <<<'BLADE'
<div class="flex flex-wrap gap-2">
    <x-lazy-btn primary>Primary</x-lazy-btn>
    <x-lazy-btn secondary>Secondary</x-lazy-btn>
    <x-lazy-btn accent>Accent</x-lazy-btn>
    <x-lazy-btn outline>Outline</x-lazy-btn>
    <x-lazy-btn ghost>Ghost</x-lazy-btn>
</div>
BLADE,
                ],
            ],
            'badge' => [
                [
                    'title' => 'Badge variants',
                    'code' => <<<'BLADE'
<div class="flex flex-wrap gap-2">
    <x-lazy-badge primary label="Primary" />
    <x-lazy-badge success label="Success" />
    <x-lazy-badge warning label="Warning" />
    <x-lazy-badge error label="Error" />
    <x-lazy-badge outline label="Outline" />
</div>
BLADE,
                ],
            ],
            default => [],
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
