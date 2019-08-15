[![Downloads this Month](https://img.shields.io/packagist/dm/damejidlo/newrelic.svg)](https://packagist.org/packages/damejidlo/newrelic)
[![Latest Stable Version](https://poser.pugx.org/damejidlo/newrelic/v/stable)](https://github.com/damejidlo/newrelic/releases)
![](https://travis-ci.org/damejidlo/newrelic.svg?branch=master)

# Install
```
composer require damejidlo/newrelic
```

# Configure
1. In your `BasePresenter` add `use PresenterProfiler;`.
2. Into `index.php` put `$_ENV` settings (something like this):
```php
<?php

$_ENV['REQUEST_TIME_FLOAT'] = microtime(TRUE);

$container = require __DIR__ . '/../app/bootstrap.php';

$_ENV['COMPILATION_TIME_FLOAT'] = microtime(TRUE);

$container->getByType(\Nette\Application\Application::class)->run();
```
