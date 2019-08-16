<?php
declare(strict_types = 1);

namespace DamejidloTests;

use Nette\StaticClass;
use Tester\Assert;



class FunctionMocks
{

	use StaticClass;

	/**
	 * @var int[]
	 */
	private static $called = [];

	/**
	 * @var mixed[][]
	 */
	private static $expected = [];

	/**
	 * @var string
	 */
	private static $namespace;



	public static function setup(string $namespace) : void
	{
		self::$called = [];
		self::$expected = [];
		self::$namespace = $namespace;
	}



	/**
	 * @param string $name
	 * @param mixed[] $args
	 */
	public static function expect(string $name, array $args) : void
	{
		self::$expected[self::getFullFunctionName($name)][] = $args;
	}



	/**
	 * @param string $fullName
	 * @param mixed[] $args
	 */
	public static function assertCall(string $fullName, array $args) : void
	{
		if (!isset(self::$expected[$fullName])) {
			Assert::fail("Function '{$fullName}' was not expected.");
		}

		$callIndex = self::$called[$fullName] ?? 0;
		$expectedArgs = self::$expected[$fullName][$callIndex] ?? end(self::$expected[$fullName]);

		Assert::same($expectedArgs, $args, "Arguments of '{$fullName}' function call");

		self::recordCall($fullName);
	}



	private static function recordCall(string $fullName) : void
	{
		if (!isset(self::$called[$fullName])) {
			self::$called[$fullName] = 0;
		}

		self::$called[$fullName]++;
	}



	public static function close() : void
	{
		foreach (self::$expected as $functionName => $expectations) {
			Assert::same(count($expectations), self::$called[$functionName] ?? 0, "Expected number of calls of '{$functionName}' function");
		}
	}



	public static function functionExists(string $name) : bool
	{
		return isset(self::$expected[self::getFullFunctionName($name)]);
	}



	protected static function getFullFunctionName(string $name) : string
	{
		return self::$namespace . "\\$name";
	}

}
