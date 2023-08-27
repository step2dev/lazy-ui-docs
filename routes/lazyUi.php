<?php

use App\Livewire\Docs\GetStarted;
use App\Livewire\Docs\Loading;

Route::redirect('/', 'docs/getting-started');
Route::get('getting-started', GetStarted::class)->name('getting-started');
Route::get('loading', Loading::class)->name('loading');

