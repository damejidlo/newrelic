<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\ApplicationName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\ApplicationName\ModularApplicationNameProvider;
use Damejidlo\NewRelic\ApplicationName\SimpleApplicationNameProvider;
use DamejidloTests\DjTestCase;
use Tester\Assert;



/**
 * @testCase
 */
class ModularApplicationNameProviderTest extends DjTestCase
{

	/**
	 * @dataProvider getDataForApplicationName
	 * @param string $applicationName
	 * @param string $moduleName
	 * @param string $expectedName
	 */
	public function testApplicationName(string $applicationName, string $moduleName, string $expectedName) : void
	{
		$applicationNameProvider = new SimpleApplicationNameProvider($applicationName);
		$applicationModuleNameProvider = new DummyApplicationModuleNameProvider($moduleName);
		$modularApplicationNameProvider = new ModularApplicationNameProvider($applicationNameProvider, $applicationModuleNameProvider);

		Assert::same($modularApplicationNameProvider->getApplicationName(), $expectedName);
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForApplicationName() : array
	{
		return [
			[
				'applicationName' => 'app',
				'moduleName' => 'module',
				'expectedName' => 'app/module',
			],
			[
				'applicationName' => 'app',
				'moduleName' => '',
				'expectedName' => 'app',
			],
		];
	}

}



(new ModularApplicationNameProviderTest())->run();
