<?php

namespace Damejidlo\NewRelic;

use Exception;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Object;
use Nette\Utils\Strings;



class NewRelicProfilingListener extends Object implements Subscriber
{

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



	/**
	 * @param string $appUrl
	 * @param Container $container
	 * @param Client $client
	 */
	public function __construct($appUrl, Container $container, Client $client)
	{
		$this->appUrl = $appUrl;
		$this->container = $container;
		$this->client = $client;
	}



	/**
	 * @return string[]
	 */
	public function getSubscribedEvents()
	{
		return [
			'Nette\\Application\\Application::onStartup',
			'Nette\\Application\\Application::onShutdown',
			'Nette\\Application\\Application::onRequest',
			'Nette\\Application\\Application::onResponse',
		];
	}



	/**
	 * @param Application $app
	 */
	public function onStartup(Application $app)
	{
		$_ENV['APP_STARTUP_TIME_FLOAT'] = microtime(TRUE);
		$this->client->disableAutorum();

		$this->client->customTimeMetric(
			'Nette/CompilationTime',
			$_ENV['COMPILATION_TIME_FLOAT'],
			$_ENV['REQUEST_TIME_FLOAT']
		);
		$this->client->customTimeMetric(
			'Nette/StartupTime',
			$_ENV['APP_STARTUP_TIME_FLOAT'],
			$_ENV['COMPILATION_TIME_FLOAT']
		);

		$this->client->addCustomTracer('Nette\Application\Routers\RouteList::match');
		$this->client->addCustomTracer('Nette\Application\UI\Presenter::createRequest');
		$this->client->addCustomTracer('Nette\Application\UI\Presenter::run');
		$this->client->addCustomTracer('Nette\Application\Responses\TextResponse::send');
		$this->client->addCustomTracer('Doctrine\ORM\EntityManager::flush');
	}



	/**
	 * @param Application $app
	 * @param Request $request
	 */
	public function onRequest(Application $app, Request $request)
	{
		if (!empty($request->parameters['exception']) && $request->parameters['exception'] instanceof Exception) {
			return;
		}

		$this->setCustomParametersToClient($request->getParameters());

		$_ENV['APP_REQUEST_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetric(
			'Nette/RequestTime',
			$_ENV['APP_REQUEST_TIME_FLOAT'],
			$_ENV['APP_STARTUP_TIME_FLOAT']
		);

		$this->handleRequest($request);

		$this->client->customTimeMetric(
			'Nette/RequestTime',
			$_ENV['APP_REQUEST_TIME_FLOAT'],
			$_ENV['APP_STARTUP_TIME_FLOAT']
		);
		$this->client->customTimeMetric(
			'Nette/CompilationTime',
			$_ENV['COMPILATION_TIME_FLOAT'],
			$_ENV['REQUEST_TIME_FLOAT']
		);
		$this->client->customTimeMetric(
			'Nette/StartupTime',
			$_ENV['APP_STARTUP_TIME_FLOAT'],
			$_ENV['COMPILATION_TIME_FLOAT']
		);
	}



	/**
	 * @param Application $app
	 * @param IResponse $response
	 */
	public function onResponse(Application $app, IResponse $response)
	{
		$_ENV['APP_RESPONSE_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetric(
			'Nette/ResponseTime',
			$_ENV['APP_RESPONSE_TIME_FLOAT'],
			$_ENV['APP_REQUEST_TIME_FLOAT']
		);

		$presenter = $app->getPresenter();

		if ($presenter && $presenter instanceof Presenter) {
			$module = $this->getModule($presenter->getName());

			$this->client->customTimeMetric(
				"Presenter/{$module}/Shutdown",
				$_ENV['APP_PRESENTER_LEAVE'],
				$_ENV['APP_PRESENTER_SEND_RESPONSE']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/InitGlobals",
				$_ENV['APP_PRESENTER_REQUIREMENTS_BEGIN'],
				$_ENV['APP_PRESENTER_BEFORE_INIT']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/Startup",
				$_ENV['APP_PRESENTER_STARTUP_END'],
				$_ENV['APP_PRESENTER_REQUIREMENTS_BEGIN']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/Action",
				$_ENV['APP_PRESENTER_ACTION_END'],
				$_ENV['APP_PRESENTER_ACTION_BEGIN']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/Render",
				$_ENV['APP_PRESENTER_RENDER_END'],
				$_ENV['APP_PRESENTER_RENDER_BEGIN']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/BeforeRender",
				$_ENV['APP_PRESENTER_RENDER_BEGIN'],
				$_ENV['APP_PRESENTER_ACTION_END']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/ProcessSignal",
				$_ENV['APP_PRESENTER_SIGNAL_END'],
				$_ENV['APP_PRESENTER_SIGNAL_BEGIN']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/AfterRender",
				$_ENV['APP_PRESENTER_AFTER_RENDER_END'],
				$_ENV['APP_PRESENTER_RENDER_END']
			);
			$this->client->customTimeMetric(
				"Presenter/{$module}/SendTemplate",
				$_ENV['APP_PRESENTER_SEND_TEMPLATE_END'],
				$_ENV['APP_PRESENTER_SEND_TEMPLATE_BEGIN']
			);
		}
	}



	/**
	 * @param Application $app
	 */
	public function onShutdown(Application $app)
	{
		$_ENV['APP_SHUTDOWN_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetric(
			'Nette/ResponseSendingTime',
			$_ENV['APP_SHUTDOWN_TIME_FLOAT'],
			$_ENV['APP_RESPONSE_TIME_FLOAT']
		);

		if (function_exists("apc_cache_info")) {
			$apcInfo = apc_cache_info('user', TRUE);
			if (isset(
				$apcInfo['nslots'],
				$apcInfo['nmisses'],
				$apcInfo['ninserts'],
				$apcInfo['nentries'],
				$apcInfo['nexpunges'],
				$apcInfo['nhits']
			)) {
				$this->client->customMetric('Apc/Slots', $apcInfo['nslots']);
				$this->client->customMetric('Apc/Misses', $apcInfo['nmisses']);
				$this->client->customMetric('Apc/Inserts', $apcInfo['ninserts']);
				$this->client->customMetric('Apc/Entries', $apcInfo['nentries']);
				$this->client->customMetric('Apc/Expunges', $apcInfo['nexpunges']);
				$this->client->customMetric('Apc_Total/Hits', $apcInfo['nhits']);
			}
			if (isset($apcInfo['mem_size'])) {
				$this->client->customMetric('Apc_Total/Memory_Usage', $apcInfo['mem_size'] / 1024);
			}
		}
	}



	/**
	 * @param string $presenterName
	 * @return string
	 */
	protected function getModule($presenterName)
	{
		$modules = explode(':', Strings::trim($presenterName, ':'));
		$module = reset($modules) ?: '';
		$module = $module === 'Nette' ? 'Front' : $module;

		return $module;
	}



	/**
	 * @return string
	 */
	protected function resolveCliTransactionName()
	{
		return '$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1));
	}



	/**
	 * @param Request $request
	 * @param string[] $params
	 * @return string
	 */
	protected function resolveTransactionName(Request $request, $params)
	{
		return (
			$request->getPresenterName()
			. (isset($params['action']) ? ':' . $params['action'] : '')
			. (isset($params['do']) ? '?signal=' . preg_replace('~[0-9]+~', '*', $params['do']) : '')
		);
	}



	/**
	 * @param array $params
	 */
	protected function setCustomParametersToClient(array $params)
	{
		foreach ($params as $name => $value) {
			if (is_scalar($value)) {
				$this->client->addCustomParameter($name, $value);
			}
		}
	}



	protected function handleCliRequest()
	{
		$this->client->setAppname("{$this->appUrl}/Cron");
		$this->client->nameTransaction($this->resolveCliTransactionName());
		$this->client->backgroundJob(TRUE);
	}



	/**
	 * @param Request $request
	 */
	protected function handleWebRequest(Request $request)
	{
		$module = $this->getModule($request->getPresenterName());
		$this->client->setAppname($this->appUrl . ($module ? "/{$module}" : ''));
		if ($module === 'Cron') {
			$this->client->backgroundJob(TRUE);
		}
		$params = $request->getParameters() + $request->getPost();
		$this->transactionName = $this->resolveTransactionName($request, $params);
		$this->client->nameTransaction($this->transactionName);
	}



	/**
	 * @param Request $request
	 */
	protected function handleRequest(Request $request)
	{
		if (PHP_SAPI === 'cli') {
			$this->handleCliRequest();
		} else {
			$this->handleWebRequest($request);
		}
	}

}
