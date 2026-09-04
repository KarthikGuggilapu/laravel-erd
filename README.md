# Laravel ERD

Interactive database schema visualization and developer tooling for Laravel applications.

Laravel ERD scans Laravel migrations and Eloquent models, builds a structured schema registry, and provides an interactive ERD interface for understanding tables, columns, indexes, foreign keys, models, and relationships.

> **Status:** Early development / Git installation
> Laravel ERD is currently distributed directly through Git and is not yet published on Packagist.

---

## Requirements

* PHP 8.2+
* Laravel 11, 12, or 13
* Composer
* Git

---

# Installation

Currently, Laravel ERD is installed directly from its Git repository.

## 1. Add the Git Repository

From your Laravel application's root directory, open:

```text
composer.json
```

Add the package repository:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KarthikGuggilapu/laravel-erd"
        }
    ]
}
```

Replace:

```text
https://github.com/YOUR_USERNAME/laravel-erd
```

with the actual Git repository URL.

For example:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/example/laravel-erd"
        }
    ]
}
```

---

## 2. Require the Package

Once the repository has been added:

```bash
composer require karthikguggilapu/laravel-erd:dev-main
```

If your repository uses another branch, replace `dev-main` accordingly.

For example:

```bash
composer require karthikguggilapu/laravel-erd:dev-develop
```

Composer will clone the package from Git and install it into:

```text
vendor/karthikguggilapu/laravel-erd
```

---

# Local Package Development

If you are developing Laravel ERD itself and want to test it against a Laravel application on the same computer, a Composer path repository is recommended.

Suppose your folders look like:

```text
Projects/
├── laravel-erd/
└── test-laravel-app/
```

Inside `test-laravel-app/composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-erd"
        }
    ]
}
```

Then run:

```bash
composer require karthikguggilapu/laravel-erd:@dev
```

Composer will use your local package directory instead of downloading it from Git.

This is the recommended setup while developing the package.

---

# Install the Package

After Composer has installed the package:

```bash
php artisan erd:install
```

You should see:

```text
Laravel ERD installed successfully.

ERD URL: /erd
Storage: storage/erd
```

---

# Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=erd-config
```

This creates:

```text
config/erd.php
```

The default configuration is:

```php
<?php

return [
    'enabled' => env(
        'ERD_ENABLED',
        !app()->environment('production')
    ),

    'route' => [
        'prefix' => 'erd',
        'middleware' => [],
    ],

    'storage' => [
        'path' => storage_path('erd'),
    ],
];
```

---

# Enable / Disable Laravel ERD

For local development:

```env
ERD_ENABLED=true
```

For production:

```env
ERD_ENABLED=false
```

If `ERD_ENABLED` is not specified, Laravel ERD automatically enables itself outside the production environment.

---

# Refresh the Schema

After installation:

```bash
php artisan erd:refresh
```

This scans the Laravel application's migrations and updates the ERD registry.

The registry is stored in:

```text
storage/erd/
├── metadata.json
├── migrations.json
├── models.json
├── relations.json
├── history.json
└── layout.json
```

---

# Open the ERD

Start the Laravel application:

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000/erd
```

Or simply:

```text
/erd
```

---

# Current Development Workflow

While developing Laravel ERD:

```text
laravel-erd/
        │
        │ Git
        ▼
GitHub Repository
        │
        │ Composer VCS
        ▼
Laravel Test Application
        │
        ├── php artisan erd:install
        │
        ├── php artisan erd:refresh
        │
        └── /erd
```

For rapid local development, use a Composer path repository:

```text
laravel-erd/
      ▲
      │
      │ local path
      │
test-laravel-app/
```

This means you can modify the package source and immediately test the changes in the Laravel application.

---

# Updating the Git Version

When using the Git repository:

```bash
composer update karthikguggilapu/laravel-erd
```

Or reinstall the latest development branch:

```bash
composer require karthikguggilapu/laravel-erd:dev-main
```

---

# Package Structure

```text
laravel-erd/
├── config/
│   └── erd.php
│
├── resources/
│   └── views/
│       └── erd.blade.php
│
├── routes/
│   └── web.php
│
├── src/
│   ├── Commands/
│   │   ├── InstallCommand.php
│   │   └── RefreshCommand.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ErdController.php
│   │   └── Middleware/
│   │       └── EnsureErdEnabled.php
│   │
│   ├── Services/
│   │   ├── RegistryManager.php
│   │   └── MigrationScanner.php
│   │
│   └── ErdServiceProvider.php
│
├── composer.json
└── README.md
```

---

# Registry

Laravel ERD uses JSON files as a registry for the visualization layer.

```text
database/migrations
        │
app/Models
        │
        ▼
   ERD Scanners
        │
        ▼
 JSON Registry
        │
        ▼
 Interactive ERD
```

The JSON registry is not the source of truth.

Laravel migration and model files remain the source of truth.

---

# Current Features

* Package service provider
* Configuration
* Local-development protection
* `erd:install`
* `erd:refresh`
* JSON registry
* Migration scanning foundation
* Initial ERD interface
* `/erd` route

---

# Roadmap

## Schema Intelligence

* [x] Package foundation
* [x] Registry manager
* [x] Initial migration scanner
* [ ] Robust migration parser
* [ ] Migration execution status
* [ ] Migration batch detection
* [ ] Deleted migration detection
* [ ] Migration change detection
* [ ] Model scanner
* [ ] Relationship scanner

## ERD Interface

* [ ] Interactive table cards
* [ ] Drag and drop
* [ ] Zoom
* [ ] Pan
* [ ] SVG relationship lines
* [ ] Relationship animations
* [ ] Search
* [ ] Table inspector
* [ ] Relationship highlighting

## Developer Commands

* [ ] `php artisan erd make:model Product -m`
* [ ] `php artisan erd make:controller ProductController`
* [ ] `php artisan erd migrate`
* [ ] Automatic registry updates

## Advanced

* [ ] Schema history
* [ ] Schema snapshots
* [ ] Database/schema comparison
* [ ] Migration drift detection
* [ ] Manual relationship overrides
* [ ] Registry versioning
* [ ] Automated tests
* [ ] Packagist release

---

# License

MIT License.
