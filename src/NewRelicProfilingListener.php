<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Exception;
use Nette\Application\Application;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\Strings;



class NewRelicProfilingListener
{

	use SmartObject;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $transactionName;

	/**
	 * @var string
	 */
	protected $appUrl;



	public function __construct(string $appUrl, Container $container, Client $client)
	{
		$this->appUrl = $appUrl;
		$this->container = $container;
		$this->client = $client;
	}



	public function onStartup(Application $app) : void
	{
		$_ENV['APP_STARTUP_TIME_FLOAT'] = microtime(TRUE);

		$this->client->customTimeMetricFromEnv(
			'Nette/CompilationTime',
			'COMPILATION_TIME_FLOAT',
			'REQUEST_TIME_FLOAT'
		);
		$this->client->customTimeMetricFromEnv(
			'Nette/StartupTime',
			'APP_STARTUP_TIME_FLOAT',
			'COMPILATION_TIME_FLOAT'
		);
	}



	public function onRequest(Application $app, Request $request) : void
	{
		if (isset($request->parameters['exception']) && $request->parameters['exception'] instanceof Exception) {
			return;
		}

		$this->setCustomParametersToClient($request->getParameters());

		$_ENV['APP_REQUEST_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/RequestTime',
			'APP_REQUEST_TIME_FLOAT',
			'APP_STARTUP_TIME_FLOAT'
		);

		$this->handleRequest($request);
	}



	public function onResponse(Application $app, IResponse $response) : void
	{
		$_ENV['APP_RESPONSE_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/ResponseTime',
			'APP_RESPONSE_TIME_FLOAT',
			'APP_REQUEST_TIME_FLOAT'
		);
	}



	public function onShutdown(Application $app) : void
	{
		$_ENV['APP_SHUTDOWN_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/ResponseSendingTime',
			'APP_SHUTDOWN_TIME_FLOAT',
			'APP_RESPONSE_TIME_FLOAT'
		);
	}



	protected function getModule(string $presenterName) : string
	{
		$modules = explode(':', Strings::trim($presenterName, ':'));
		$module = reset($modules) ?: '';
		$module = $module === 'Nette' ? 'Front' : $module;

		return $module;
	}



	protected function resolveCliTransactionName() : string
	{
		return '$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1));
	}



	/**
	 * @param Request $request
	 * @param string[] $params
	 * @return string
	 */
	protected function resolveTransactionName(Request $request, array $params) : string
	{
		return (
			$request->getPresenterName()
			. (isset($params['action']) ? ':' . $params['action'] : '')
			. (isset($params['do']) ? '?signal=' . preg_replace('~[0-9]+~', '*', $params['do']) : '')
		);
	}



	/**
	 * @param mixed[] $params
	 */
	protected function setCustomParametersToClient(array $params) : void
	{
		foreach ($params as $name => $value) {
			if (is_scalar($value)) {
				$this->client->addCustomParameter($name, $value);
			}
		}
	}



	protected function handleCliRequest() : void
	{
		$this->client->nameTransaction($this->resolveCliTransactionName());
		$this->client->backgroundJob(TRUE);
	}



	protected function handleWebRequest(Request $request) : void
	{
		$module = $this->getModule($request->getPresenterName());
		if ($module === 'Cron') {
			$this->client->backgroundJob(TRUE);
		}
		$params = $request->getParameters() + $request->getPost();
		$this->transactionName = $this->resolveTransactionName($request, $params);
		$this->client->nameTransaction($this->transactionName);
	}



	protected function handleRequest(Request $request) : void
	{
		if (PHP_SAPI === 'cli') {
			$this->handleCliRequest();
		} else {
			$this->handleWebRequest($request);
		}
	}

}
