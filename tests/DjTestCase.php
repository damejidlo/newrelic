<?php
declare(strict_types = 1);

namespace DamejidloTests;

use Tester\TestCase;



class DjTestCase extends TestCase
{

	protected function setUp() : void
	{
		parent::setUp();

		FunctionMocks::setup('Damejidlo\NewRelic');
	}



	protected function tearDown() : void
	{
		parent::tearDown();

		FunctionMocks::close();
	}



	public function run() : void
	{
		if ($_ENV['IS_PHPSTAN']) {
			return;
		}
		parent::run();
	}

}
