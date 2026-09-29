<?php

use App\Livewire\Docs\ComponentPage;
use App\Livewire\Docs\Guide;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/docs/getting-started');

Route::get('{page}', Guide::class)
    ->whereIn('page', ['getting-started', 'upgrade', 'themes', 'livewire'])
    ->name('guide');

Route::get('components/{slug}', ComponentPage::class)
    ->name('component');
