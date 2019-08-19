<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\Utils;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\Utils\PathUtils;
use DamejidloTests\DjTestCase;
use Tester\Assert;



/**
 * @testCase
 */
class PathUtilsTest extends DjTestCase
{

	public function testBaseDirIsInvalid() : void
	{
		$pathUtils = new PathUtils('/invalid-base-dir');
		Assert::exception(
			function () use ($pathUtils) : void {
				$pathUtils->normalize(__DIR__);
			},
			\UnexpectedValueException::class,
			"Could not determine real path of '/invalid-base-dir'."
		);
	}



	public function testPathIsInvalid() : void
	{
		$pathUtils = new PathUtils(__DIR__);
		Assert::exception(
			function () use ($pathUtils) : void {
				$pathUtils->normalize('/invalid-path');
			},
			\UnexpectedValueException::class,
			"Could not determine real path of '/invalid-path'."
		);
	}



	/**
	 * @dataProvider getPaths
	 * @param string $path
	 * @param string $expectedPath
	 */
	public function testNormalizePath(string $path, string $expectedPath) : void
	{
		chdir(__DIR__ . '/../../../');
		$pathUtils = new PathUtils(__DIR__ . '/../../');
		Assert::same($expectedPath, $pathUtils->normalize($path));
	}



	/**
	 * @return mixed[]
	 */
	protected function getPaths() : array
	{
		return [
			[
				'path' => './tests/NewRelic',
				'expectedPath' => 'NewRelic',
			],
			[
				'path' => __FILE__,
				'expectedPath' => 'NewRelic/Utils/PathUtilsTest.phpt',
			],
			[
				'path' => 'src',
				'expectedPath' => realpath(__DIR__ . '/../../../src'),
			],
		];
	}

}



(new PathUtilsTest())->run();
