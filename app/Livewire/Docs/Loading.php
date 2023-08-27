<?php

namespace App\Livewire\Docs;

use Livewire\Component;

class Loading extends Component
{
    public function render()
    {
        return view('livewire.docs.loading')
            ->layoutData([
                'title' => 'Loading',
            ]);
    }
}
