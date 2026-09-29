<?php

namespace App\View\Components;

use Closure;
use Step2dev\LazyUI\LazyComponent;

class Code extends LazyComponent
{
    public function render(): \Illuminate\Contracts\View\View|Closure
    {
        return function (array $data) {
            $attributes = $this->getAttributesFromData($data);
            $attributes['language'] ??= 'text';
            $attributes['code'] ??= trim((string) $data['slot']);

            $data['attributes'] = $attributes;

            return view('components.code', $data)->render();
        };
    }
}
