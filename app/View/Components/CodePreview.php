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
            $attributes['output'] = null;

            if ($attributes->get('render', true)) {
                try {
                    $wrappedCode = '<div data-doc-preview-root class="contents">'.$code.'</div>';
                    $rendered = Blade::render($wrappedCode, deleteCachedView: true);

                    $attributes['preview'] = $rendered;
                    $attributes['output'] = trim((string) preg_replace(
                        [
                            '/<!--.*?-->/s',
                            '/^<div data-doc-preview-root class="contents">/s',
                            '/<\/div>$/s',
                        ],
                        '',
                        trim($rendered)
                    ));
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
