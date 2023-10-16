<?php

use App\Livewire\Docs\GetStarted;
use App\Livewire\Docs\Loading;
use App\Livewire\Docs\RichText;

Route::redirect('/', 'docs/getting-started');
Route::get('getting-started', GetStarted::class)->name('getting-started');
Route::get('loading', Loading::class)->name('loading');
Route::get('richtext', RichText::class)->name('richtext');
