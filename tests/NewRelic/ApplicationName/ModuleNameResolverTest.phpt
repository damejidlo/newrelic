<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\ApplicationName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\ApplicationName\ModuleNameResolver;
use DamejidloTests\DjTestCase;
use Tester\Assert;



/**
 * @testCase
 */
class ModuleNameResolverTest extends DjTestCase
{

	/**
	 * @dataProvider getDataForResolveModuleName
	 * @param string $identifier
	 * @param string $expectedModuleName
	 */
	public function testResolveModuleName(string $identifier, string $expectedModuleName) : void
	{
		$moduleNameResolver = new ModuleNameResolver();
		$moduleNameByIdentifierPrefix = [
			'/foo/bar' => 'FooBar',
			'/foo' => 'Foo',
			'/baz' => 'Baz',
		];
		Assert::same($expectedModuleName, $moduleNameResolver->resolveModuleName($moduleNameByIdentifierPrefix, $identifier));
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForResolveModuleName() : array
	{
		return [
			[
				'identifier' => 'whatever',
				'expectedModuleName' => '',
			],
			[
				'identifier' => '/foo/bar/42',
				'expectedModuleName' => 'FooBar',
			],
			[
				'identifier' => '/foo/lorem/ipsum/1',
				'expectedModuleName' => 'Foo',
			],
		];
	}

}



(new ModuleNameResolverTest())->run();
