<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\ApplicationName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\ApplicationName\ConsoleApplicationModuleNameProvider;
use Damejidlo\NewRelic\ApplicationName\ModuleNameResolver;
use Damejidlo\NewRelic\Utils\PathUtils;
use DamejidloTests\DjTestCase;
use Tester\Assert;



/**
 * @testCase
 */
class ConsoleApplicationModuleNameProviderTest extends DjTestCase
{

	private const BASE_DIR = __DIR__ . '/../../..';



	/**
	 * @dataProvider getDataForApplicationName
	 * @param string[] $argv
	 * @param string $expectedName
	 */
	public function testModuleName(array $argv, string $expectedName) : void
	{
		$_SERVER['argv'] = $argv;
		chdir(self::BASE_DIR);
		$pathUtils = new PathUtils(self::BASE_DIR);
		$moduleNameResolver = new ModuleNameResolver();
		$moduleNameByCommandPrefix = ['vendor/nette/tester/src/tester ' => 'Tests', '' => 'Console'];

		$consoleApplicationModuleNameProvider = new ConsoleApplicationModuleNameProvider($pathUtils, $moduleNameResolver, $moduleNameByCommandPrefix);

		Assert::same($consoleApplicationModuleNameProvider->getModuleName(), $expectedName);
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForApplicationName() : array
	{
		return [
			[
				'argv' => ['./vendor/bin/tester', 'tests'],
				'expectedName' => 'Tests',
			],
			[
				'argv' => ['tests/bootstrap.php'],
				'expectedName' => 'Console',
			],
		];
	}

}



(new ConsoleApplicationModuleNameProviderTest())->run();
