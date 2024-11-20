<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Filament::serving(function () {
        //     Filament::registerNavigationItems([\Filament\Navigation\NavigationItem::make('Create Sales')
        //     ->url(route('filament.admin.resources.sales.create'))
        //     ->group('Sales')
        //     ->icon('heroicon-o-plus')]);
        // });
    }
}
