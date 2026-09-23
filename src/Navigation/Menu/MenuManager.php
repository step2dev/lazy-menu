<?php

namespace Step2dev\LazyMenu\Navigation\Menu;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LogicException;
use Throwable;

/** @phpstan-consistent-constructor */
class MenuManager extends Collection
{
    protected Menu $menu;

    private ?Closure $builder = null;

    private MenuRegistry $registry;

    private bool $built = false;

    private ?array $visibleItems = null;

    private int $visibleItemsCount = 0;

    public function __construct(Menu $menu, $items = [], ?MenuRegistry $registry = null)
    {
        $this->menu = $menu;
        $this->registry = $registry ?? app(MenuRegistry::class);
        parent::__construct($items);
    }

    public function buildUsing(Closure $builder): static
    {
        if ($this->built) {
            throw new LogicException('Menu builder must be registered before rendering.');
        }

        $this->builder = $builder;
        $this->built = false;
        $this->visibleItems = null;

        return $this;
    }

    public function register(
        Closure $contributor,
        ?string $id = null,
        int $priority = 0,
        ?string $before = null,
        ?string $after = null,
        ?string $group = null,
    ): static {
        if ($this->built) {
            throw new LogicException('Menu contributors must be registered before rendering.');
        }

        $this->registry->register($contributor, $id, $priority, $before, $after, $group);

        return $this;
    }

    public function order(string $id, ?int $priority = null, ?string $before = null, ?string $after = null): static
    {
        if ($this->built) {
            throw new LogicException('Menu contributors must be positioned before rendering.');
        }

        $this->registry->order($id, $priority, $before, $after);

        return $this;
    }

    public function useView(string $view): static
    {
        $this->registry->useView($view);

        return $this;
    }

    public function group(string $label): static
    {
        return $this->push(['group' => $label]);
    }

    public function addItem(
        string $route,
        ?string $label = null,
        ?string $icon = null,
        ?array $children = null,
        ?string $permission = null,
        ?string $iconView = null,
        ?array $parameters = null,
        mixed $badge = null,
    ): static {
        $label ??= $route;
        $children ??= [];

        if ($children) {
            $children = array_map(static function ($child) {
                if ($child instanceof Menu) {
                    return $child->toArray()[0] ?? $child->toArray();
                }

                return $child;
            }, $children);
        }

        return $this->push([
            ...compact('route', 'label', 'icon', 'permission', 'children'),
            'icon_view' => $iconView,
            'parameters' => $parameters ?? [],
            'badge' => $badge,
        ]);
    }

    public function createMenu(string $url, ?string $label = null): Menu
    {
        return $this->menu->make($url, $label);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function filter(?callable $callback = null): static
    {
        if ($callback) {
            return new static(new Menu, Arr::where($this->items, $callback));
        }

        return new static(new Menu, array_filter($this->items));
    }

    public function visibleItems(): array
    {
        if ($this->visibleItems !== null && $this->visibleItemsCount === count($this->items)) {
            return $this->visibleItems;
        }

        if (! $this->built) {
            $this->built = true;
            if ($this->builder !== null) {
                ($this->builder)($this);
            }

            foreach ($this->registry->contributors() as $contributor) {
                $contributor($this);
            }
        }

        $this->visibleItemsCount = count($this->items);

        return $this->visibleItems = $this->visible($this->items);
    }

    private function visible(array $items): array
    {
        $visible = [];

        foreach ($items as $item) {
            if ($item instanceof Menu) {
                $item = $item->toArray()[0] ?? [];
            }

            if (! is_array($item)) {
                continue;
            }

            $permission = $item['permission'] ?? null;

            if ($permission && ! auth()->user()?->can($permission)) {
                continue;
            }

            if (($item['badge'] ?? null) instanceof Closure) {
                $item['badge'] = $item['badge']();
            }

            $children = $item['children'] ?? $item['submenu'] ?? [];
            $item['children'] = $this->visible($children instanceof Menu ? $children->toArray() : $children);
            $routeName = $item['route'] ?? null;
            $item['active'] = $routeName && Route::has($routeName) && request()->routeIs($routeName);

            if ($item['active'] && ! empty($item['parameters'])) {
                $item['active'] = request()->url() === route($routeName, $item['parameters']);
            }
            foreach ($item['children'] as $child) {
                if ($child['active'] ?? false) {
                    $item['active'] = true;
                    break;
                }
            }
            $visible[] = $item;
        }

        return $visible;
    }

    /**
     * @throws Throwable
     */
    public function render(?string $view = null): string
    {
        /** @var view-string $template */
        $template = $view ?? $this->registry->view() ?? 'lazy-menu::menu-generator';

        return view($template, [
            'menuItems' => $this->visibleItems(),
        ])->render();
    }
}
