<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component
{
    public function render(): View
    {
        return view('livewire.home')->layout('components.layouts.guest', [
            'title' => 'Lazy UI — Laravel Blade & Livewire components',
            'description' => 'Lazy UI 2.x documentation for Laravel 12/13, Livewire 4, Tailwind CSS 4 and daisyUI 5.',
        ]);
    }
}
