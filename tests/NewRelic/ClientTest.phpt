<?php

namespace Damejidlo\NewRelic {

	require_once __DIR__ . '/../bootstrap.php';
	require_once __DIR__ . '/../FunctionMocks.php';

	use DamejidloTests\NewRelic\FunctionMocks;



	/**
	 * @param string $name
	 * @param array $args
	 */
	function call_user_func_array($name, array $args)
	{
		FunctionMocks::assertCall(__NAMESPACE__ . "\\$name", $args);
	}

	function newrelic_add_custom_parameter()
	{
		FunctionMocks::assertCall(__FUNCTION__, func_get_args());
	}

}

namespace DamejidloTests\NewRelic {

	require_once __DIR__ . '/../bootstrap.php';
	require_once __DIR__ . '/../FunctionMocks.php';

	use Damejidlo\NewRelic\Client;
	use Tester\Environment;
	use Tester\TestCase;



	/**
	 * @testCase
	 */
	class ClientTest extends TestCase
	{

		public function testMethodsVia__call()
		{
			$client = new Client();

			$args = ['key1', 'value1'];
			FunctionMocks::expect('newrelic_add_custom_parameter', $args);
			$client->addCustomParameter(...$args);

			Environment::$checkAssertions = FALSE;
		}



		protected function tearDown()
		{
			parent::tearDown();

			FunctionMocks::close();
		}



		protected function setUp()
		{
			parent::setUp();

			FunctionMocks::setup('Damejidlo\NewRelic');
		}

	}



	(new ClientTest())->run();

}



