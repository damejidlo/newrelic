<?php
declare(strict_types = 1);

namespace DamejidloTests;

use Tester\TestCase;



class DjTestCase extends TestCase
{

	public function run() : void
	{
		if ($_ENV['IS_PHPSTAN']) {
			return;
		}
		parent::run();
	}

}
