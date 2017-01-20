<?php

namespace Damejidlo\NewRelic;

use Nette\SmartObject;



/**
 * @method setAppname($name, $license = NULL, $xmit = FALSE)
 * @method noticeError($message, $exception = NULL)
 * @method nameTransaction($name)
 * @method endOfTransaction()
 * @method endTransaction($ignore = FALSE)
 * @method startTransaction($appname, $license = NULL)
 * @method ignoreTransaction()
 * @method ignoreApdex()
 * @method backgroundJob($flag)
 * @method captureParams($enable)
 * @method addCustomParameter($key, $value)
 * @method addCustomTracer($callback)
 * @method getBrowserTimingHeader($flag = TRUE)
 * @method getBrowserTimingFooter($flag = TRUE)
 * @method disableAutorum()
 * @method setUserAttributes($user, $account, $product)
 */
class Client
{

	use SmartObject {
		__call as traitCall;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function customMetric($name, $value)
	{
		$this->__call(__FUNCTION__, ['Custom/' . $name, $value]);
	}



	/**
	 * @param string $name
	 * @param int $second
	 * @param int $first
	 */
	public function customTimeMetric($name, &$second, &$first)
	{
		if (empty($second) || empty($first)) {
			return;
		}

		$this->customMetric($name, round(abs($second - $first) * 1000, 0));
	}



	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$function = 'newrelic_' . self::convertCamelCaseToUnderscore($name);

		if (!extension_loaded('newrelic')) {
			return FALSE;
		}

		if (!function_exists($function)) {
			return $this->traitCall($name, $args);
		}

		return call_user_func_array($function, $args);
	}



	/**
	 * camelCaseAction name -> under_score
	 *
	 * @param string $text
	 * @return string
	 */
	private static function convertCamelCaseToUnderscore($text)
	{
		$text = preg_replace('#(.)(?=[A-Z])#', '$1_', $text);
		$text = strtolower($text);
		$text = rawurlencode($text);

		return $text;
	}

}
