<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use Step2dev\LazyUI\LazyComponent;

class CodePreview extends LazyComponent
{
    public function __construct()
    {
    }

    public function render(): View|Closure
    {
        return function (array $data) {
            $attributes = $this->getAttributesFromData($data);
            $title = $attributes->get('title');
            $attributes['id'] ??= str()->slug($title);
            $attributes['href'] ??= '#'.$attributes['id'];
            $code = $attributes->get('code', (string) $data['slot']);
            $code = html_entity_decode($code);
            $preview = Blade::render($code, deleteCachedView: true);
            $attributes['code'] = $code;
            $attributes['preview'] = $preview;

            $data['attributes'] = $attributes;

            return view('components.code-preview', $data)->render();
        };
    }
}
