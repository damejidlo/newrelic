<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic;

require_once __DIR__ . '/../bootstrap.php';

use Damejidlo\NewRelic\Client;
use DamejidloTests\DjTestCase;
use DamejidloTests\FunctionMocks;
use Tester\Assert;



/**
 * @testCase
 */
class ClientTest extends DjTestCase
{

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
