<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Observers\CategoryActivityObserver;
use App\Observers\CompanyActivityObserver;
use App\Observers\InventoryActivityObserver;
use App\Observers\LocationActivityObserver;
use App\Observers\ProductActivityObserver;
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
        Product::observe(ProductActivityObserver::class);
        Inventory::observe(InventoryActivityObserver::class);
        Company::observe(CompanyActivityObserver::class);
        ProductCategory::observe(CategoryActivityObserver::class);
        Location::observe(LocationActivityObserver::class);
    }
}
