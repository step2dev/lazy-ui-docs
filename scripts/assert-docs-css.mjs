import { readFileSync } from 'node:fs';

const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
const entry = manifest['resources/css/app.css'];

if (!entry?.file) {
    console.error('Vite CSS entry not found in manifest.');
    process.exit(1);
}

const css = readFileSync('public/build/' + entry.file, 'utf8');

const requiredSelectors = [
    '.alert', '.aura', '.avatar', '.badge', '.breadcrumbs', '.btn', '.card', '.carousel',
    '.chat', '.checkbox', '.collapse', '.countdown', '.diff', '.divider', '.dock', '.drawer',
    '.dropdown', '.fab', '.fieldset', '.file-input', '.filter', '.footer', '.hero', '.hover-3d',
    '.hover-gallery', '.indicator', '.input', '.join', '.kbd', '.label', '.list', '.loading',
    '.mask', '.megamenu', '.menu', '.mockup-browser', '.mockup-code', '.mockup-phone',
    '.mockup-window', '.modal', '.navbar', '.otp', '.progress', '.radial-progress', '.radio',
    '.range', '.rating', '.select', '.skeleton', '.stack', '.stat', '.stats', '.status',
    '.step', '.steps', '.swap', '.tab', '.tabs', '.table', '.text-rotate', '.textarea',
    '.theme-controller', '.timeline', '.toast', '.toggle', '.tooltip', '.validator',
    '.btn-soft', '.btn-dash', '.rating-half', '.mask-half-1', '.mask-half-2',
    '.modal-start', '.indicator-bottom', '.dock-xl', '.otp-primary', '.step-error',
    '.table-pin-cols',
];

const missing = requiredSelectors.filter((selector) => !css.includes(selector));

if (missing.length) {
    console.error('Missing compiled component styles:', missing.join(', '));
    process.exit(1);
}

console.log(`Compiled Lazy UI styles OK (${requiredSelectors.length} selectors).`);
