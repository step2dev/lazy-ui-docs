<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Guide extends Component
{
    public string $page = 'getting-started';

    public array $guide = [];

    public function mount(string $page = 'getting-started'): void
    {
        $this->page = $page;
        $this->guide = config("docs.guides.{$page}", []);

        abort_if($this->guide === [], 404);
    }

    public function render(): View
    {
        return view('livewire.docs.guide')
            ->layout('components.layouts.docs', [
                'title' => $this->guide['title'],
                'description' => $this->guide['description'],
            ]);
    }
}
