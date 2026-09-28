<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Step2dev\LazyUI\LazyComponent;

class CodePreview extends LazyComponent
{
    public function render(): View|Closure
    {
        return function (array $data) {
            $attributes = $this->getAttributesFromData($data);
            $title = (string) $attributes->get('title', '');
            $attributes['id'] ??= str()->slug($title ?: 'example');
            $attributes['href'] ??= '#'.$attributes['id'];

            $code = html_entity_decode((string) $attributes->get('code', (string) $data['slot']));
            $attributes['code'] = $code;

            $attributes['preview'] = null;

            if ($attributes->get('render', true)) {
                try {
                    $attributes['preview'] = Blade::render($code, deleteCachedView: true);
                } catch (\Throwable) {
                    // Some documentation snippets intentionally contain application variables.
                    // Keep those examples highlighted without breaking the documentation page.
                }
            }

            $data['attributes'] = $attributes;

            return view('components.code-preview', $data)->render();
        };
    }
}
