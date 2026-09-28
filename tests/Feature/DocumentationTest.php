<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
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

    public function test_component_catalog_has_no_duplicate_slugs(): void
    {
        $slugs = collect(config('docs.categories', []))
            ->flatMap(fn (array $components): array => array_keys($components));

        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    public function test_catalog_covers_major_lazy_ui_families(): void
    {
        $categories = array_keys(config('docs.categories', []));

        foreach (['Actions', 'Data display', 'Navigation', 'Feedback', 'Inputs', 'Forms', 'Layout', 'Mockup'] as $category) {
            $this->assertContains($category, $categories);
        }
    }
}
