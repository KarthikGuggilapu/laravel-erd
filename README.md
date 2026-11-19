Laravel ERD

Interactive database schema visualization and developer tooling for Laravel applications.

Laravel ERD scans Laravel migrations and Eloquent models, builds a structured schema registry, and provides an interactive ERD interface for understanding tables, columns, indexes, foreign keys, models, and relationships.

Status: Early development / Git installation

Laravel ERD is currently distributed directly through GitHub and is not yet published on Packagist.

Requirements

PHP 8.2+

Laravel 11, 12, or 13

Composer

Git

Installation

Laravel ERD is currently installed directly from its GitHub repository.

For Laravel application developers: Use the Git/VCS installation below.

Do not add a Composer path repository unless you are developing Laravel ERD itself.

1. Add the Git Repository

From the root directory of your Laravel application, open:

composer.json

Add the repository:

{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KarthikGuggilapu/laravel-erd"
        }
    ]
}

If your composer.json already contains a repositories section, add Laravel ERD to the existing array.

2. Require the Package

Run:

composer require karthikguggilapu/laravel-erd:dev-main

Composer installs the package into:

vendor/karthikguggilapu/laravel-erd

Important: The Composer path repository shown later is only for Laravel ERD package development. External developers should use the GitHub/VCS installation.

3. Install Laravel ERD

php artisan erd:install

4. Publish Configuration

php artisan vendor:publish --tag=erd-config

This creates:

config/erd.php

Default configuration:

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

5. Enable Laravel ERD

For local development:

ERD_ENABLED=true

For production:

ERD_ENABLED=false

If ERD_ENABLED is not specified, Laravel ERD automatically enables itself outside production.

6. Analyze Your Laravel Application

php artisan erd:refresh

Laravel ERD scans:

database/migrations
app/Models

and builds the ERD registry.

7. Open the ERD

Start Laravel:

php artisan serve

Open:

http://127.0.0.1:8000/erd

Installation Flow

For a normal Laravel application developer:

Laravel Application
        |
        v
Add GitHub VCS repository
        |
        v
composer require karthikguggilapu/laravel-erd:dev-main
        |
        v
vendor/karthikguggilapu/laravel-erd
        |
        v
php artisan erd:install
        |
        v
php artisan vendor:publish --tag=erd-config
        |
        v
Configure ERD_ENABLED
        |
        v
php artisan erd:refresh
        |
        v
/erd

GitHub Installation vs Local Development

These are two different workflows.



GitHub Installation

Local Development

Intended for

Laravel application developers

Laravel ERD contributors

Composer type

vcs

path

Source

GitHub

Local directory

Downloads package

Yes

No

Uses local package source

No

Yes

Immediate source changes

No

Yes

Recommended for external developers

Yes

No

GitHub installation

{
    "type": "vcs",
    "url": "https://github.com/KarthikGuggilapu/laravel-erd"
}

Local development

{
    "type": "path",
    "url": "../laravel-erd"
}

External Laravel developers should use the GitHub/VCS installation.

Local Package Development

For Laravel ERD contributors and package developers only.

Normal Laravel application developers should skip this section.

If you are developing Laravel ERD itself and want to test local changes against a Laravel application, use a Composer path repository.

Example:

Projects/
|
|-- laravel-erd/
|
`-- test-laravel-app/

1. Configure the Local Path Repository

Open:

test-laravel-app/composer.json

Add:

{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-erd"
        }
    ]
}

Then run from the test application:

composer require karthikguggilapu/laravel-erd:@dev

Composer uses the local package directory instead of downloading it from GitHub.

2. Develop and Test

laravel-erd/
      |
      | Composer path repository
      v
test-laravel-app/
      |
      v
php artisan erd:install
      |
      v
php artisan erd:refresh
      |
      v
/erd

When you modify the package source, you can immediately test the changes in the Laravel test application.

Updating Laravel ERD

When using the GitHub repository:

composer update karthikguggilapu/laravel-erd

Then refresh the registry:

php artisan erd:refresh

Registry

Laravel ERD uses JSON files as a registry for the visualization layer.

Laravel Migrations
        |
        +----------------+
        |                |
        v                v
Migration Scanner   Model Scanner
        |                |
        +-------+--------+
                |
                v
       Relationship Scanner
                |
                v
          JSON Registry
                |
                v
        Interactive ERD

Registry location:

storage/erd/

Typical files:

storage/erd/
|
|-- metadata.json
|-- migrations.json
|-- models.json
|-- relations.json
|-- history.json
`-- layout.json

The JSON registry is not the source of truth.

Laravel migrations and Eloquent models remain the source of truth.

Refreshing the Registry

Whenever migrations or models change:

php artisan erd:refresh

Then reload:

/erd

Route

Default route:

/erd

Example:

http://127.0.0.1:8000/erd

The route prefix can be configured in:

config/erd.php

Security

Laravel ERD is primarily intended as a development and debugging tool.

For production environments, keep ERD disabled unless you explicitly need it.

Recommended:

ERD_ENABLED=false

Current Features

Composer package foundation

Laravel service provider

Configuration support

Local-development protection

erd:install

erd:refresh

JSON registry

Migration scanning foundation

Eloquent model scanning foundation

Relationship scanning foundation

Interactive ERD interface

Table filtering

Table search

Relationship visualization

Zoom and pan

Table history

Schema analysis

Roadmap

Schema Intelligence

Package foundation

Registry manager

Initial migration scanner

Model scanner foundation

Relationship scanner foundation

Robust migration parser

Migration execution status

Migration batch detection

Deleted migration detection

Migration change detection

Advanced relationship detection

Polymorphic relationship detection

Dynamic relationship resolution

Manual relationship overrides

Migration drift detection

ERD Interface

Interactive table cards

Drag and drop

Zoom

Pan

SVG relationship lines

Relationship animations

Search

Table filtering

Relationship highlighting

Table history

Advanced table inspector

Improved automatic layout

Export functionality

Developer Commands

php artisan erd make:model Product -m

php artisan erd make:controller ProductController

php artisan erd migrate

Automatic registry updates

Advanced

Schema history improvements

Schema snapshots

Database/schema comparison

Migration drift detection

Manual relationship overrides

Registry versioning

Automated tests

Laravel version compatibility testing

Packagist release

Stable release

Package Structure

laravel-erd/
|
|-- config/
|   `-- erd.php
|
|-- resources/
|   `-- views/
|       `-- index.blade.php
|
|-- routes/
|   `-- web.php
|
|-- src/
|   |-- Commands/
|   |   |-- InstallCommand.php
|   |   |-- RefreshCommand.php
|   |   `-- UninstallCommand.php
|   |
|   |-- Http/
|   |   |-- Controllers/
|   |   |   `-- ErdController.php
|   |   `-- Middleware/
|   |       `-- EnsureErdEnabled.php
|   |
|   |-- Services/
|   |   |-- RegistryManager.php
|   |   |-- MigrationScanner.php
|   |   |-- ModelScanner.php
|   |   `-- RelationScanner.php
|   |
|   `-- ErdServiceProvider.php
|
|-- composer.json
|-- README.md
|-- LICENSE
`-- CHANGELOG.md

Troubleshooting

Composer cannot find the package

Make sure the GitHub repository is added to composer.json:

{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KarthikGuggilapu/laravel-erd"
        }
    ]
}

Then:

composer clear-cache
composer require karthikguggilapu/laravel-erd:dev-main

ERD route is not available

Check:

ERD_ENABLED=true

Then:

php artisan optimize:clear
php artisan erd:refresh

Open:

/erd

Schema is not updated

Run:

php artisan erd:refresh

Laravel ERD generates its registry from the application's migrations and models.

Contributing

Contributions, bug reports, feature requests, and improvements are welcome.

Recommended contributor workflow:

Clone Laravel ERD
        |
        v
Create / use a Laravel test application
        |
        v
Configure Composer path repository
        |
        v
Develop locally
        |
        v
Test with Laravel application
        |
        v
Commit changes
        |
        v
Push to GitHub

License

Laravel ERD is open-sourced software licensed under the MIT license.