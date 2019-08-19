<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Damejidlo\NewRelic\TransactionName\NetteWebTransactionNameProvider;
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

	/**
	 * @var NetteWebTransactionNameProvider
	 */
	private $netteWebTransactionNameProvider;



	public function __construct(Client $client, NetteWebTransactionNameProvider $netteWebTransactionNameProvider)
	{
		$this->client = $client;
		$this->netteWebTransactionNameProvider = $netteWebTransactionNameProvider;
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

		$_ENV['APP_REQUEST_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/RequestTime',
			'APP_REQUEST_TIME_FLOAT',
			'APP_STARTUP_TIME_FLOAT'
		);

		if (PHP_SAPI !== 'cli') {
			$this->client->nameTransaction($this->netteWebTransactionNameProvider->getTransactionName($request));
		}
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

}
