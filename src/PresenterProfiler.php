<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\BadSignalException;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings;



trait PresenterProfiler
{

	/**
	 * @var int[]
	 */
	private $methodCalls = [
		'loadState' => 0,
		'saveGlobalState' => 0,
	];



	/**
	 * $_ENV['APP_PRESENTER_LEAVE'] - $_ENV['APP_PRESENTER_ENTER'] = run()
	 * $_ENV['APP_PRESENTER_LEAVE'] - $_ENV['APP_PRESENTER_SEND_RESPONSE'] = shutdown()
	 *
	 * @param Request $request
	 * @return IResponse|NULL
	 */
	public function run(Request $request)
	{
		$_ENV['APP_PRESENTER_ENTER'] = microtime(TRUE);

		try {
			$response = parent::run($request);
		} finally {
			$_ENV['APP_PRESENTER_LEAVE'] = microtime(TRUE);
		}

		return $response;
	}



	/**
	 * At the end of setParent, the globals init is called
	 *
	 * @param IContainer|NULL $parent
	 * @param string|NULL $name
	 * @return static
	 */
	public function setParent(IContainer $parent = NULL, $name = NULL)
	{
		parent::setParent($parent, $name);

		$_ENV['APP_PRESENTER_BEFORE_INIT'] = microtime(TRUE);

		return $this;
	}



	/**
	 * First load state is called right before checkRequirements()
	 *
	 * $_ENV['APP_PRESENTER_REQUIREMENTS_BEGIN'] - $_ENV['APP_PRESENTER_BEFORE_INIT'] = initGlobalParameters()
	 *
	 * @param array $params
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->methodCalled('loadState', 'APP_PRESENTER_REQUIREMENTS_BEGIN');
	}



	/**
	 * @param string $method
	 * @param string $envKey
	 */
	private function methodCalled(string $method, string $envKey)
	{
		$this->methodCalls[$method] += 1;

		if ($this->methodCalls[$method] === 1) {
			$_ENV[$envKey] = microtime(TRUE);
		}
	}



	/**
	 * action is after startup
	 *
	 * $_ENV['APP_PRESENTER_STARTUP_END'] - $_ENV['APP_PRESENTER_REQUIREMENTS_BEGIN'] = checkRequirements() + startup()
	 * $_ENV['APP_PRESENTER_ACTION_END'] - $_ENV['APP_PRESENTER_ACTION_BEGIN'] = action<default>()
	 * $_ENV['APP_PRESENTER_RENDER_END'] - $_ENV['APP_PRESENTER_RENDER_BEGIN'] = render<default>()
	 * $_ENV['APP_PRESENTER_RENDER_BEGIN'] - $_ENV['APP_PRESENTER_ACTION_END'] = beforeRender()
	 *
	 * @param string $method
	 * @param array $params
	 * @return bool
	 */
	protected function tryCall($method, array $params) : bool
	{
		$isAction = Strings::startsWith($method, 'action');
		$isRender = Strings::startsWith($method, 'render');

		if ($isAction) {
			$_ENV['APP_PRESENTER_STARTUP_END'] = microtime(TRUE);
			$_ENV['APP_PRESENTER_ACTION_BEGIN'] = microtime(TRUE);

		} elseif ($isRender) {
			$_ENV['APP_PRESENTER_RENDER_BEGIN'] = microtime(TRUE);
		}

		try {
			$result = parent::tryCall($method, $params);

		} finally {
			if ($isAction) {
				$_ENV['APP_PRESENTER_ACTION_END'] = microtime(TRUE);

			} elseif ($isRender) {
				$_ENV['APP_PRESENTER_RENDER_END'] = microtime(TRUE);
			}
		}

		return $result;
	}



	/**
	 * $_ENV['APP_PRESENTER_SIGNAL_END'] - $_ENV['APP_PRESENTER_SIGNAL_BEGIN'] = processSignal()
	 *
	 * @throws BadSignalException
	 */
	public function processSignal()
	{
		$_ENV['APP_PRESENTER_SIGNAL_BEGIN'] = microtime(TRUE);
		try {
			parent::processSignal();
		} finally {
			$_ENV['APP_PRESENTER_SIGNAL_END'] = microtime(TRUE);
		}
	}



	/**
	 * $_ENV['APP_PRESENTER_AFTER_RENDER_END'] - $_ENV['APP_PRESENTER_RENDER_END'] = afterRender()
	 */
	protected function saveGlobalState()
	{
		$this->methodCalled('saveGlobalState', 'APP_PRESENTER_AFTER_RENDER_END');

		parent::saveGlobalState();
	}



	/**
	 * $_ENV['APP_PRESENTER_SEND_TEMPLATE_END'] - $_ENV['APP_PRESENTER_SEND_TEMPLATE_BEGIN'] = sendTemplate()
	 *
	 * @throws BadRequestException
	 * @throws AbortException
	 */
	public function sendTemplate()
	{
		$_ENV['APP_PRESENTER_SEND_TEMPLATE_BEGIN'] = microtime(TRUE);

		try {
			parent::sendTemplate();

		} finally {
			$_ENV['APP_PRESENTER_SEND_TEMPLATE_END'] = microtime(TRUE);
		}
	}



	/**
	 * @param IResponse $response
	 * @throws AbortException
	 */
	public function sendResponse(IResponse $response)
	{
		$_ENV['APP_PRESENTER_SEND_RESPONSE'] = microtime(TRUE);

		parent::sendResponse($response);
	}

}
