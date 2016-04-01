<?php

namespace DamejidloTests\NewRelic;

use Nette\Object;
use Tester\Assert;



class FunctionMocks extends Object
{

	/**
	 * @var int[]
	 */
	private static $called = [];

	/**
	 * @var array
	 */
	private static $expected = [];

	/**
	 * @var string
	 */
	private static $namespace;



	/**
	 * @param string $namespace
	 */
	public static function setup($namespace)
	{
		self::$called = [];
		self::$expected = [];
		self::$namespace = $namespace;
	}



	/**
	 * @param string $name
	 * @param array $args
	 */
	public static function expect($name, array $args)
	{
		self::$expected[self::getFullFunctionName($name)] = $args;
	}



	/**
	 * @param string $fullName
	 * @param array $args
	 */
	public static function assertCall($fullName, array $args)
	{
		self::recordCall($fullName);

		if (!isset(self::$expected[$fullName])) {
			Assert::fail("Function '{$fullName}' was not expected.");
		}

		if (self::$expected[$fullName] !== $args) {
			Assert::fail("Function '{$fullName}' was called with unexpected arguments.");
		}

	}



	/**
	 * @param string $fullName
	 */
	private static function recordCall($fullName)
	{
		if (!isset(self::$called[$fullName])) {
			self::$called[$fullName] = 0;
		}

		self::$called[$fullName]++;
	}



	public static function close()
	{
		foreach (self::$expected as $functionName => $expectedArgs) {
			if (!isset(self::$called[$functionName])) {
				Assert::fail("Function '{$functionName}' was expected, but not called");
			}
		}
	}



	/**
	 * @param string $name
	 * @return bool
	 */
	public static function functionExists($name)
	{
		return isset(self::$expected[self::getFullFunctionName($name)]);
	}



	/**
	 * @param string $name
	 * @return string
	 */
	protected static function getFullFunctionName($name)
	{
		return self::$namespace . "\\$name";
	}

}
