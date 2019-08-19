<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\TransactionName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\TransactionName\ConsoleTransactionNameProvider;
use Damejidlo\NewRelic\Utils\PathUtils;
use DamejidloTests\DjTestCase;
use Tester\Assert;



/**
 * @testCase
 */
class ConsoleTransactionNameProviderTest extends DjTestCase
{

	private const BASE_DIR = __DIR__ . '/../../..';



	/**
	 * @dataProvider getDataForApplicationName
	 * @param string[] $argv
	 * @param string $expectedName
	 */
	public function testTransactionNAme(array $argv, string $expectedName) : void
	{
		$_SERVER['argv'] = $argv;
		chdir(self::BASE_DIR);
		$pathUtils = new PathUtils(self::BASE_DIR);

		$consoleTransactionNameProvider = new ConsoleTransactionNameProvider($pathUtils);

		Assert::same($consoleTransactionNameProvider->getTransactionName(), $expectedName);
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForApplicationName() : array
	{
		return [
			[
				'argv' => ['./vendor/bin/tester', 'tests', '--flag'],
				'expectedName' => '$ vendor/nette/tester/src/tester tests --flag',
			],
			[
				'argv' => ['tests/bootstrap.php'],
				'expectedName' => '$ tests/bootstrap.php',
			],
		];
	}

}



(new ConsoleTransactionNameProviderTest())->run();
