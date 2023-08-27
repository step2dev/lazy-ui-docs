<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Loading extends Component
{
    public function render(): View
    {
        return view('livewire.docs.loading')
            ->layoutData([
                'title' => 'Loading',
            ]);
    }
}
