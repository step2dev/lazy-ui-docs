<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ComponentPage extends Component
{
    public string $slug;

    public string $category;

    public array $component = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        foreach (config('docs.categories', []) as $category => $components) {
            if (isset($components[$slug])) {
                $this->category = $category;
                $this->component = $components[$slug];

                return;
            }
        }

        abort(404);
    }

    public function render(): View
    {
        return view('livewire.docs.component-page')
            ->layout('components.layouts.docs', [
                'title' => $this->component['name'],
                'description' => $this->component['description'],
            ]);
    }
}
