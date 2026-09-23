# Lazy Menu

[![Latest Version on Packagist](https://img.shields.io/packagist/v/step2dev/lazy-menu.svg?style=flat-square)](https://packagist.org/packages/step2dev/lazy-menu)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/step2dev/lazy-menu/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/step2dev/lazy-menu/actions/workflows/run-tests.yml?query=branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/step2dev/lazy-menu/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/step2dev/lazy-menu/actions/workflows/fix-php-code-style-issues.yml?query=branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/step2dev/lazy-menu.svg?style=flat-square)](https://packagist.org/packages/step2dev/lazy-menu)

Dynamic Laravel navigation for applications and independent modules. PHP 8.2+, Laravel 10–13.

## Install from Git

Until tagged releases are available, add a Composer VCS repository to the root application's `composer.json`:

```json
{
    "repositories": [
        {"type": "vcs", "url": "https://github.com/step2dev/lazy-menu.git"}
    ],
    "require": {
        "step2dev/lazy-menu": "dev-main"
    }
}
```

Until a tagged release is available, use `dev-main`. Laravel automatically discovers the package service provider.

## Register navigation

```php
use Step2dev\LazyMenu\Facades\Menu;
use Step2dev\LazyMenu\Navigation\Menu\MenuManager;

Menu::register(function (MenuManager $menu): void {
    $menu->addItem(
        'admin.blog.index',
        'Articles',
        iconView: 'icons.articles',
        permission: 'blog.view',
    );
}, id: 'blog', priority: 20, group: 'admin.menu.blog');

Menu::register(function (MenuManager $menu): void {
    $menu->addItem(
        'admin.pages.index',
        'Pages',
        permission: 'pages.view',
        badge: fn () => 5,
    );
}, id: 'pages', after: 'blog');
```

The `group` and item label are optional. `permission` accepts a single Laravel ability, an array of abilities (visible if **any** passes), or a callback receiving the authenticated user for custom logic. Items with a permission are hidden from guests; badge callbacks for hidden items are not evaluated.

```php
// Any of these permissions is enough:
$menu->addItem('admin.blog.index', 'Articles', permission: ['blog.view', 'blog.manage']);

// Require both permissions and an application-specific condition:
$menu->addItem(
    'admin.pages.index',
    'Pages',
    permission: fn ($user): bool => $user->can('pages.view')
        && $user->can('pages.publish')
        && $user->active,
);

// The same options work on nested items:
$menu->createMenu('admin.blog.create', 'Create')
    ->permission(fn ($user): bool => $user->can('blog.create') && $user->is_editor);
``` Without a label, the default Blade template translates the route name with `__($route)`, so define keys such as `admin.blog.index` and `admin.pages.index` in your translation files. Without a translation, Laravel displays the route key. The group label is translated the same way. Existing explicit labels and manual `$menu->push(['group' => 'Blog'])` calls remain supported.

Use `Menu::order('pages', before: 'blog')` in the host application's provider to change the order without editing installed modules. A lower priority appears first, and equal priorities preserve registration order. A missing anchor module is ignored: `Menu::order('pages', before: 'blog')` still shows Pages if Blog is absent. If Blog is registered later, the relative order applies automatically. Overrides for modules that never register are ignored. Cycles between installed modules throw a logic exception. Registration callbacks run once per request, when the menu renders. Cache database results inside callbacks where needed and filter by permissions with the `permission` argument.

Legacy `buildUsing(...)`, `Menu::addItem(...)`, `Menu::createMenu(...)` and `Menu::render()` remain available. The package renders named routes with optional `parameters`, nested items, Blade SVG `iconView` icons, static or callback `badge` values and group labels. Do not pass arbitrary user-controlled view names to `iconView`.

## Change the menu template

For a custom layout, choose a Blade view in an application's service provider; no config file is needed:

```php
use Step2dev\LazyMenu\Facades\Menu;

Menu::useView('admin.navigation.menu');
```

The view receives `$menuItems`. To choose a view for one render, call `Menu::render('admin.navigation.compact')`; it takes precedence over `useView()`. The default is `lazy-menu::menu-generator`, styled with Tailwind utility classes and no DaisyUI dependency. When your Tailwind build does not scan vendor files, add `@source "../../vendor/step2dev/lazy-menu/resources/views/**/*.blade.php";` to `resources/css/app.css` for Tailwind v4 (adjust the relative path for your CSS entry point). For Tailwind v3, include `./vendor/step2dev/lazy-menu/resources/views/**/*.blade.php` in `content` in `tailwind.config.js`. Applications using lazy-admin render its separate DaisyUI template by default.

To change the default menu, item or label markup, copy the corresponding package Blade file from `vendor/step2dev/lazy-menu/resources/views/` into the application's Laravel view override directory:

```text
resources/views/vendor/lazy-menu/menu-generator.blade.php
resources/views/vendor/lazy-menu/menu-item.blade.php
resources/views/vendor/lazy-menu/menu-label.blade.php
```

Override only the files you need. Laravel loads them instead of the package versions; item and label views receive `$item`. Keep labels and badge text escaped and leave permission filtering in the manager. Pass view names from trusted application code.

## Tests

```bash
composer test
composer analyse
```
