# Lumberjack Site Health

This package provides a simple way to register custom checks for the Site Health feature introduced in WordPress 5.2.

Once installed, register the Service Provider in `config/app.php`:

```php
'providers' => [
    ...

    Rareloop\Lumberjack\SiteHealth\SiteHealthServiceProvider::class,

    ...
],
```

## Config
You register custom checks in the `config/sitehealth.php` file:

```php
return [
    'checks' => [
        \App\SiteHealth\MyCustomCheck::class,
    ],
];
```

## Creating a check
Create a class that extends the `Rareloop\Lumberjack\SiteHealth\HealthCheck` class and register it in the config as above.

Example:

```php
<?php

namespace App\SiteHealth;

use Rareloop\Lumberjack\SiteHealth\HealthCheck;

class MyCustomCheck extends HealthCheck
{
    public function identifier(): string
    {
        return 'my-custom-check';
    }

    public function label(): string
    {
        return __('My Custom Check');
    }

    public function execute(): array
    {
        return [
            'label' => 'My custom function test',
            'description' => 'The callback to this test worked',
            'badge' => [
                'label' => 'Performance',
                'color' = 'blue',
            ],
            'status' => 'good', // 'good'|'recommended'|'critical'
            'test' => $this->identifier(),
        ];
    }
}
```

Details of what the `execute()` method should return can be found in the [WordPress 5.2 release notes](https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/).

### Setting async or direct
By default all checks will be registered as `async`. If you'd like it to run directly instead, add the following method to your class:

```php
public function type()
{
    return static::DIRECT;
}
```