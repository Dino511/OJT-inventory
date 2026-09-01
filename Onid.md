# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start dev server (Laravel server + queue listener + Vite, via `artisan dev`)
composer run dev

# Run the full test suite
composer run test
# equivalent to:
php artisan test

# Run a single test file / test method
php artisan test tests/Feature/ProductTest.php
php artisan test --filter=test_method_name

# Run pending migrations
php artisan migrate

# Code style (Laravel Pint)
vendor/bin/pint

# Tinker REPL
php artisan tinker
```

Tests run against **SQLite in-memory** (`phpunit.xml` forces `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), independent of whatever `.env` is configured for local dev.

## Architecture

This is a Laravel 13 inventory management app (companies → locations/categories/products → inventory stock levels). Auth is Laravel's built-in session guard (`AuthController` + `Auth::attempt`), all app routes gated behind `auth` middleware in `routes/web.php`; `guest` middleware guards `/login` and `/register`.

Views are plain Blade + Bootstrap 5 (loaded from CDN in `resources/views/layouts/app.blade.php`), not the Tailwind/Vite frontend scaffolded in `package.json` — Vite is wired up but the actual UI styling is Bootstrap with a custom "GitHub-style" theme (`.btn-github`, `.navbar-github`, etc. in the layout's `<style>` block).

**Database:** local dev targets SQL Server (`sqlsrv` driver, see `config/database.php`), not the `.env.example` default of SQLite. Several models compensate for SQL Server quirks:
- `Company` uses a non-standard primary key `company_id` (not `id`) and has an accessor (`getCompanyIdAttribute`) working around column-casing differences.
- Some `belongsTo`/`hasMany` relations hardcode `company_id` as both local and foreign key since it isn't the default `id`.
- A cascade-delete was deliberately removed on `inventories.location_id` (see migration `2026_08_24_000007`) to avoid SQL Server's multiple-cascade-path error.

**Data-model history (mostly resolved, but the orphaned tables still exist — don't resurrect them):**
- The `BaseUnit` and `UnitOfMeasure` models both map to the same `base_units` table (`UnitOfMeasure::$table` is overridden to `'base_units'`) — this is intentional and is the live table backing the "Base Units" CRUD (`base-units.*` routes → `UnitOfMeasureController`) and the product "Base Unit" picker. A separate `unit_of_measures` table also exists from an earlier migration attempt and is **unused/orphaned** — never validate or query against it.
- `ProductCategory` (table `product_categories`, default `id` PK) is the live category model, used by `ProductController` and wired to the `categories.*` routes via `ProductCategoryController`. The old duplicate `Category` model (which pointed at the same table but with a nonexistent `category_id` PK) was deleted. A third, unrelated empty `categories` table (id + timestamps only) also exists from a later migration and isn't used by any controller — leave it alone.
- `Inventory` (table `inventories`) uses the default `id` primary key; an earlier `$primaryKey = 'inventory_id'` override pointed at a column that was never actually created and has been removed. Its `product()`/`location()` relations use the default owner key (`id`) on `products`/`locations`. `InventoryController` implements full CRUD (`inventory.*` routes); enforce one stock record per (product, location) pair via the `Rule::unique('inventories')->where(...)` validation in `store()`/`update()`, not a raw DB unique-violation catch.
- The `inventories` table has two overlapping quantity columns — `quantity_on_hand` (original, unused) and `quantity` (added later, the one actually used by the model/controller/views). Use `quantity`.

When adding a feature touching units, categories, or locations, grep for all models/tables with similar names first — this codebase has a history of abandoned/duplicate attempts at the same concept, and some orphaned tables/models from that history are still on disk.
