<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Exception;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\Strings;



class NewRelicProfilingListener implements Subscriber
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



	/**
	 * @param string $appUrl
	 * @param Container $container
	 * @param Client $client
	 */
	public function __construct(string $appUrl, Container $container, Client $client)
	{
		$this->appUrl = $appUrl;
		$this->container = $container;
		$this->client = $client;
	}



	/**
	 * @inheritdoc
	 */
	public function getSubscribedEvents() : array
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
		$this->client->customTimeMetricFromEnv(
			'Nette/RequestTime',
			'APP_REQUEST_TIME_FLOAT',
			'APP_STARTUP_TIME_FLOAT'
		);

		$this->handleRequest($request);

		$this->client->customTimeMetricFromEnv(
			'Nette/RequestTime',
			'APP_REQUEST_TIME_FLOAT',
			'APP_STARTUP_TIME_FLOAT'
		);
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



	/**
	 * @param Application $app
	 * @param IResponse $response
	 */
	public function onResponse(Application $app, IResponse $response)
	{
		$_ENV['APP_RESPONSE_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/ResponseTime',
			'APP_RESPONSE_TIME_FLOAT',
			'APP_REQUEST_TIME_FLOAT'
		);

		$presenter = $app->getPresenter();

		if ($presenter && $presenter instanceof Presenter) {
			$module = $this->getModule($presenter->getName());

			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/Shutdown",
				'APP_PRESENTER_LEAVE',
				'APP_PRESENTER_SEND_RESPONSE'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/InitGlobals",
				'APP_PRESENTER_REQUIREMENTS_BEGIN',
				'APP_PRESENTER_BEFORE_INIT'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/Startup",
				'APP_PRESENTER_STARTUP_END',
				'APP_PRESENTER_REQUIREMENTS_BEGIN'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/Action",
				'APP_PRESENTER_ACTION_END',
				'APP_PRESENTER_ACTION_BEGIN'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/Render",
				'APP_PRESENTER_RENDER_END',
				'APP_PRESENTER_RENDER_BEGIN'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/BeforeRender",
				'APP_PRESENTER_RENDER_BEGIN',
				'APP_PRESENTER_ACTION_END'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/ProcessSignal",
				'APP_PRESENTER_SIGNAL_END',
				'APP_PRESENTER_SIGNAL_BEGIN'
			);
			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/AfterRender",
				'APP_PRESENTER_AFTER_RENDER_END',
				'APP_PRESENTER_RENDER_END'
			);

			$this->client->customTimeMetricFromEnv(
				"Presenter/{$module}/SendTemplate",
				'APP_PRESENTER_SEND_TEMPLATE_END',
				'APP_PRESENTER_SEND_TEMPLATE_BEGIN'
			);
		}
	}



	/**
	 * @param Application $app
	 */
	public function onShutdown(Application $app)
	{
		$_ENV['APP_SHUTDOWN_TIME_FLOAT'] = microtime(TRUE);
		$this->client->customTimeMetricFromEnv(
			'Nette/ResponseSendingTime',
			'APP_SHUTDOWN_TIME_FLOAT',
			'APP_RESPONSE_TIME_FLOAT'
		);
	}



	/**
	 * @param string $presenterName
	 * @return string
	 */
	protected function getModule(string $presenterName) : string
	{
		$modules = explode(':', Strings::trim($presenterName, ':'));
		$module = reset($modules) ?: '';
		$module = $module === 'Nette' ? 'Front' : $module;

		return $module;
	}



	/**
	 * @return string
	 */
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
