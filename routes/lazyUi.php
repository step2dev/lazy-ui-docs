<?php

use App\Livewire\Docs\GetStarted;

Route::redirect('/', 'docs/getting-started');
Route::get('getting-started', GetStarted::class)->name('getting-started');
