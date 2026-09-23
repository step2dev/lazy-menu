<?php

namespace Step2dev\LazyMenu\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Step2dev\LazyMenu\Navigation\Menu\MenuManager;

/**
 * @method static MenuManager buildUsing(Closure $builder)
 * @method static MenuManager register(Closure $contributor, ?string $id = null, int $priority = 0, ?string $before = null, ?string $after = null, ?string $group = null)
 * @method static MenuManager useView(string $view)
 * @method static string render(?string $view = null)
 * @method static MenuManager order(string $id, ?int $priority = null, ?string $before = null, ?string $after = null)
 * @method static MenuManager group(string $label)
 * @method static MenuManager addItem(string $route, ?string $label = null, ?string $icon = null, ?array $children = null, string|array|Closure|null $permission = null, ?string $iconView = null, ?array $parameters = null, mixed $badge = null)
 */
class Menu extends Facade
{
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return MenuManager::class;
    }
}
