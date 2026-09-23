<?php

namespace Step2dev\LazyMenu;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Step2dev\LazyMenu\Navigation\Menu\Menu;
use Step2dev\LazyMenu\Navigation\Menu\MenuManager;
use Step2dev\LazyMenu\Navigation\Menu\MenuRegistry;

class LazyMenuServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('lazy-menu')->hasViews('lazy-menu');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(MenuRegistry::class);
        $this->app->scoped(MenuManager::class, fn () => new MenuManager(new Menu));
    }
}
