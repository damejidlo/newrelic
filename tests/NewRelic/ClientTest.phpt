<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../mockedFunctions.php';

use Damejidlo\NewRelic\Client;
use DamejidloTests\FunctionMocks;
use Tester\Assert;
use Tester\TestCase;



/**
 * @testCase
 */
class ClientTest extends TestCase
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



	public function testMethodsViaMagicCall() : void
	{
		$args = ['key1', 'value1'];
		FunctionMocks::expect('newrelic_add_custom_parameter', $args);

		$client = new Client();

		Assert::noError(
			function () use ($client, $args) : void {
				$client->addCustomParameter(...$args);
			}
		);
	}

}



(new ClientTest())->run();
