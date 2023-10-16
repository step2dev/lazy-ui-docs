<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class RichText extends Component
{
    public string $text = '';

    public function render(): View
    {
        return view('livewire.docs.richtext')
            ->layoutData([
                'title' => 'Rich Text',
            ]);
    }
}
