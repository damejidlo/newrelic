<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use DamejidloTests\FunctionMocks;



/**
 * @param string $name
 * @param mixed[] $args
 */
function call_user_func_array(string $name, array $args) : void
{
	FunctionMocks::assertCall(__NAMESPACE__ . "\\$name", $args);
}



/**
 * @param string $key
 * @param bool|float|int|string $value
 */
function newrelic_add_custom_parameter(string $key, $value) : void
{
	FunctionMocks::assertCall(__FUNCTION__, func_get_args());
}



function extension_loaded(string $name) : bool
{
	return $name === 'newrelic';
}



function function_exists(string $name) : bool
{
	return FunctionMocks::functionExists($name);
}
