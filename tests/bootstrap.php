<?php
declare(strict_types = 1);

namespace DamejidloTests;

require_once __DIR__ . '/../vendor/autoload.php';

use Tester\Environment;
use Tester\Helpers;



// detect PHPStan
if (getenv('IS_PHPSTAN') !== FALSE) {
	$_ENV['IS_PHPSTAN'] = in_array(strtolower(getenv('IS_PHPSTAN')), ['1', 'true', 'yes', 'on'], TRUE);
} else {
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	/** @var array $lastTrace */
	$lastTrace = end($trace);
	$_ENV['IS_PHPSTAN'] = (bool) preg_match('~[/\\\\]phpstan(?:\.phar)?$~', $lastTrace['file'] ?? '');
}

if (! $_ENV['IS_PHPSTAN']) {
	// configure environment
	date_default_timezone_set('Europe/Prague');
	umask(0);
	Environment::setup();
}


// create temporary directory
define('TEMP_DIR', __DIR__ . '/tmp/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));
if (getenv('IS_PHPSTAN') !== '1') {
	Helpers::purge(TEMP_DIR);
}
