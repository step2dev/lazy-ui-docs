# Lazy UI Documentation

Official documentation website for [step2dev/lazy-ui](https://github.com/step2dev/lazy-ui).

Production: https://lazyui.step2.dev/docs

## Stack

- PHP 8.2+
- Laravel 12
- Livewire 4.4+
- Lazy UI 2.x development branch
- Tailwind CSS 4
- daisyUI 5
- Vite 6

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

Open `/docs/getting-started`.

## Documentation architecture

The component catalog is defined in `config/docs.php`.

- Guides: getting started, upgrade, themes, Livewire.
- Components: every public Lazy UI component family is listed by category.
- Component pages are rendered by `App\Livewire\Docs\ComponentPage`.
- The shared responsive docs navigation is in `resources/views/components/layouts/docs.blade.php`.

When a public Lazy UI component is added, add its documentation entry to `config/docs.php` with a copy-ready Blade example.

## Build

```bash
npm run build
php artisan test
```

## Source package

Lazy UI itself lives at https://github.com/step2dev/lazy-ui and the 2.x documentation targets the `2.x-dev` branch.
