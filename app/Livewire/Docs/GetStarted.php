<?php

namespace App\Livewire\Docs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class GetStarted extends Component
{
    public const REQUIREMENTS = [
        'PHP 8.1+'        => 'https://www.php.net',
        'Composer'        => 'https://getcomposer.org',
        'Laravel 10.x'    => 'https://laravel.com',
        'Livewire 3.x'    => 'https://laravel-livewire.com',
        'Alpine.js 3.x'   => 'https://alpinejs.dev',
        'Tailwindcss 3.x' => 'https://tailwindcss.com',
        'DaisyUI 3.x'     => 'https://daisyui.com',
    ];

    public function render(): View
    {
        return view('livewire.docs.get-started', [
            'requirements' => self::REQUIREMENTS,
        ])->layoutData([
            'title' => 'Getting Started',
        ]);
    }
}
