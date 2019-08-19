<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Nette\Application\Application;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\SmartObject;



final class NetteProfilingListener
{

	use SmartObject;

	/**
	 * @var Client
	 */
	private $client;



	public function __construct(Client $client)
	{
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
		if (isset($request->parameters['exception']) && $request->parameters['exception'] instanceof \Throwable) {
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



	private function resolveCliTransactionName() : string
	{
		return '$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1));
	}



	/**
	 * @param Request $request
	 * @param string[] $params
	 * @return string
	 */
	private function resolveTransactionName(Request $request, array $params) : string
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
	private function setCustomParametersToClient(array $params) : void
	{
		foreach ($params as $name => $value) {
			if (is_scalar($value)) {
				$this->client->addCustomParameter($name, $value);
			}
		}
	}



	private function handleCliRequest() : void
	{
		$this->client->nameTransaction($this->resolveCliTransactionName());
	}



	private function handleWebRequest(Request $request) : void
	{
		$params = $request->getParameters() + $request->getPost();
		$this->client->nameTransaction($this->resolveTransactionName($request, $params));
	}



	private function handleRequest(Request $request) : void
	{
		if (PHP_SAPI === 'cli') {
			$this->handleCliRequest();
		} else {
			$this->handleWebRequest($request);
		}
	}

}
