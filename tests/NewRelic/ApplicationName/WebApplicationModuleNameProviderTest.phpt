<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\ApplicationName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\ApplicationName\ModuleNameResolver;
use Damejidlo\NewRelic\ApplicationName\WebApplicationModuleNameProvider;
use DamejidloTests\DjTestCase;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;



/**
 * @testCase
 */
class WebApplicationModuleNameProviderTest extends DjTestCase
{

	/**
	 * @dataProvider getDataForApplicationName
	 * @param string $url
	 * @param string $expectedName
	 */
	public function testModuleName(string $url, string $expectedName) : void
	{
		$urlScript = new UrlScript($url, '/');
		$request = new Request($urlScript);
		$moduleNameResolver = new ModuleNameResolver();
		$moduleNameByPathPrefix = ['/api' => 'Api', '' => 'Front'];

		$webApplicationModuleNameProvider = new WebApplicationModuleNameProvider($request, $moduleNameResolver, $moduleNameByPathPrefix);

		Assert::same($webApplicationModuleNameProvider->getModuleName(), $expectedName);
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForApplicationName() : array
	{
		return [
			[
				'url' => 'https://www.damejidlo.cz/api/endpoint',
				'expectedName' => 'Api',
			],
			[
				'url' => 'https://www.damejidlo.cz/restaurants',
				'expectedName' => 'Front',
			],
		];
	}

}



(new WebApplicationModuleNameProviderTest())->run();
