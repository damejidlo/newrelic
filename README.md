# Install
## Composer
```json
{
	"require": {
		"damejidlo/newrelic": "@dev"
	},
  	"repositories": [
		{"type": "git", "url": "git@github.com:damejidlo/newrelic.git"}
	]
}
```

## Konfigurace v projektu
1. Do `BasePresenter` dát: `use PresenterProfiler;`.
2. Do `index.php` dát následující `$_ENV` nasatvení (bude to vypadat nějak takto):
```php
<?php

$_ENV['REQUEST_TIME_FLOAT'] = microtime(TRUE);

$container = require __DIR__ . '/../app/bootstrap.php';

$_ENV['COMPILATION_TIME_FLOAT'] = microtime(TRUE);

$container->getByType('Nette\Application\Application')->run();
```


