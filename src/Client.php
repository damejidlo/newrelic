<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Nette\SmartObject;



/**
 * @method bool setAppname(string $name, ?string $license = NULL, ?bool $xmit = FALSE)
 * @method void noticeError(string $message, ?\Throwable $exception = NULL)
 * @method bool nameTransaction(string $name)
 * @method void endOfTransaction()
 * @method bool endTransaction(bool $ignore = FALSE)
 * @method bool startTransaction(string $appname, ?string $license = NULL)
 * @method void ignoreTransaction()
 * @method void ignoreApdex()
 * @method void backgroundJob(bool $flag = TRUE)
 * @method void captureParams(bool $enable = TRUE)
 * @method bool addCustomParameter(string $key, $value)
 * @method bool addCustomTracer(string $callback)
 * @method string getBrowserTimingHeader(bool $flag = TRUE)
 * @method string getBrowserTimingFooter(bool $flag = TRUE)
 * @method bool disableAutorum()
 * @method bool setUserAttributes(string $user, string $account, string $product)
 */
class Client
{

	use SmartObject {
		__call as traitCall;
	}



	public function customMetric(string $name, string $value) : bool
	{
		return $this->__call(__FUNCTION__, ['Custom/' . $name, $value]);
	}



	public function customTimeMetric(string $name, float $second, float $first) : bool
	{
		return $this->customMetric($name, (string) round(abs($second - $first) * 1000, 0));
	}



	public function customTimeMetricFromEnv(string $name, string $secondKey, string $firstKey) : bool
	{
		if (!isset($_ENV[$firstKey]) || !isset($_ENV[$secondKey])) {
			return FALSE;
		}

		return $this->customTimeMetric($name, $_ENV[$secondKey], $_ENV[$firstKey]);
	}



	/**
	 * @param string $name
	 * @param mixed[] $args
	 * @return mixed
	 */
	public function __call(string $name, array $args)
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
	private static function convertCamelCaseToUnderscore(string $text) : string
	{
		$text = preg_replace('#(.)(?=[A-Z])#', '$1_', $text);
		$text = strtolower($text);
		$text = rawurlencode($text);

		return $text;
	}

}
