<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;
use Torchlight\Middleware\RenderTorchlight;

class DocumentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(RenderTorchlight::class);
    }

    public function test_getting_started_page_renders(): void
    {
        $this->get('/docs/getting-started')
            ->assertOk()
            ->assertSee('Getting started')
            ->assertSee('Laravel 12 or 13')
            ->assertSee('Tailwind CSS 4');
    }

    public function test_every_documented_component_has_a_page(): void
    {
        foreach (config('docs.categories', []) as $components) {
            foreach ($components as $slug => $component) {
                $this->get('/docs/components/'.$slug)
                    ->assertOk()
                    ->assertSee($component['name']);
            }
        }
    }

    public function test_every_documented_component_has_variant_examples(): void
    {
        foreach (config('docs.categories', []) as $components) {
            foreach ($components as $slug => $component) {
                $this->get('/docs/components/'.$slug)
                    ->assertOk()
                    ->assertSee('Basic example')
                    ->assertSee($component['name']);
            }
        }
    }

    public function test_structural_variant_previews_are_not_empty(): void
    {
        $cases = [
            'tabs' => ['Overview', 'Details'],
            'aura' => ['Aura preview'],
            'indicator' => ['Inbox'],
            'dock' => ['Home', 'Search', 'Profile'],
            'menu-list' => ['Second item'],
            'megamenu' => ['Docs', 'Blog', 'About'],
            'join' => ['Previous', 'Current', 'Next'],
        ];

        foreach ($cases as $slug => $expected) {
            $response = $this->get('/docs/components/'.$slug)->assertOk();

            foreach ($expected as $text) {
                $response->assertSee($text);
            }
        }
    }

    public function test_component_catalog_has_no_duplicate_slugs(): void
    {
        $slugs = collect(config('docs.categories', []))
            ->flatMap(fn (array $components): array => array_keys($components));

        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    public function test_catalog_covers_major_lazy_ui_families(): void
    {
        $categories = array_keys(config('docs.categories', []));

        foreach (['Actions', 'Data display', 'Navigation', 'Feedback', 'Inputs', 'Forms', 'Layout', 'Mockup', 'Widgets'] as $category) {
            $this->assertContains($category, $categories);
        }
    }

    public function test_every_registered_lazy_ui_component_is_documented(): void
    {
        $aliases = collect(Blade::getClassComponentAliases());

        $documentedClasses = collect(config('docs.categories', []))
            ->flatMap(fn (array $components): array => collect($components)
                ->pluck('tag')
                ->map(fn (string $tag): ?string => $aliases->get('lazy-'.$tag))
                ->filter()
                ->all())
            ->unique()
            ->values();

        $registeredClasses = $aliases
            ->filter(fn (string $class, string $alias): bool => str_starts_with($alias, 'lazy-'))
            ->values()
            ->unique();

        $missing = $registeredClasses->diff($documentedClasses)->values();

        $this->assertSame([], $missing->all(), 'Undocumented Lazy UI component classes: '.$missing->implode(', '));
    }
}
