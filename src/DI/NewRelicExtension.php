<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\DI;

use Damejidlo\NewRelic\ApplicationName\ConsoleApplicationModuleNameProvider;
use Damejidlo\NewRelic\ApplicationName\IApplicationNameProvider;
use Damejidlo\NewRelic\ApplicationName\ModularApplicationNameProvider;
use Damejidlo\NewRelic\ApplicationName\ModuleNameResolver;
use Damejidlo\NewRelic\ApplicationName\SimpleApplicationNameProvider;
use Damejidlo\NewRelic\ApplicationName\WebApplicationModuleNameProvider;
use Damejidlo\NewRelic\Client;
use Damejidlo\NewRelic\NewRelicProfilingListener;
use Damejidlo\NewRelic\Utils\PathUtils;
use Nette\Application\Application;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Validators;



class NewRelicExtension extends CompilerExtension
{

	/**
	 * @var mixed[]
	 */
	private $defaults = [
		'applicationName' => '',
		'applicationModules' => [
			'web' => [],
			'console' => [],
		],
		'autorum' => FALSE,
		'customTracers' => [
			'Nette\Application\UI\Presenter::createRequest',
			'Nette\Application\UI\Presenter::run',
			'Nette\Application\Responses\TextResponse::send',
		],
	];

	/**
	 * @var bool
	 */
	private $consoleMode;



	public function __construct(bool $consoleMode)
	{
		$this->consoleMode = $consoleMode;
	}



	public function loadConfiguration() : void
	{
		$config = $this->validateConfig($this->defaults);
		Validators::assert($config['applicationName'], 'string:1..');
		Validators::assertField($config['applicationModules'], 'web');
		Validators::assert($config['applicationModules']['web'], 'string[]');
		Validators::assertField($config['applicationModules'], 'console');
		Validators::assert($config['applicationModules']['console'], 'string[]');
		Validators::assert($config['autorum'], 'bool');
		Validators::assert($config['customTracers'], 'string[]');

		$containerBuilder = $this->getContainerBuilder();

		$containerBuilder->addDefinition($this->prefix('pathUtils'))
			->setType(PathUtils::class);

		$containerBuilder->addDefinition($this->prefix('moduleNameResolver'))
			->setType(ModuleNameResolver::class);

		if ($this->consoleMode) {
			$containerBuilder->addDefinition($this->prefix('applicationModuleNameProvider'))
				->setType(ConsoleApplicationModuleNameProvider::class)
				->setArguments(['moduleNameByCommandPrefix' => $config['applicationModules']['console']]);
		} else {
			$containerBuilder->addDefinition($this->prefix('applicationModuleNameProvider'))
				->setType(WebApplicationModuleNameProvider::class)
				->setArguments(['moduleNameByPathPrefix' => $config['applicationModules']['web']]);
		}

		$containerBuilder->addDefinition($this->prefix('simpleApplicationNameProvider'))
			->setType(SimpleApplicationNameProvider::class)
			->setAutowired(SimpleApplicationNameProvider::class)
			->setArguments(['applicationName' => $config['applicationName']]);

		$containerBuilder->addDefinition($this->prefix('modularApplicationNameProvider'))
			->setType(ModularApplicationNameProvider::class)
			->setArguments(['applicationNameProvider' => $this->prefix('@simpleApplicationNameProvider')]);

		$containerBuilder->addDefinition($this->prefix('client'))
			->setType(Client::class);

		$containerBuilder->addDefinition($this->prefix('profilingListener'))
			->setType(NewRelicProfilingListener::class)
			->setArguments(['appUrl' => $config['applicationName']]);
	}



	public function beforeCompile() : void
	{
		$applicationDefintion = $this->getContainerBuilder()->getDefinitionByType(Application::class);
		$applicationDefintion->addSetup('?->onStartup[] = ?', ['@self', [$this->prefix('@profilingListener'), 'onStartup']]);
		$applicationDefintion->addSetup('?->onRequest[] = ?', ['@self', [$this->prefix('@profilingListener'), 'onRequest']]);
		$applicationDefintion->addSetup('?->onResponse[] = ?', ['@self', [$this->prefix('@profilingListener'), 'onResponse']]);
		$applicationDefintion->addSetup('?->onShutdown[] = ?', ['@self', [$this->prefix('@profilingListener'), 'onShutdown']]);
	}



	public function afterCompile(ClassType $class) : void
	{
		$config = $this->getConfig();
		$initialize = $class->getMethod('initialize');

		$initialize->addBody(
			'$this->getService(?)->setAppname($this->getByType(?)->getApplicationName());',
			[$this->prefix('client'), IApplicationNameProvider::class]
		);

		if (! (bool) $config['autorum']) {
			$initialize->addBody('$this->getService(?)->disableAutorum();', [$this->prefix('client')]);
		}

		foreach ($config['customTracers'] as $functionName) {
			$initialize->addBody('$this->getService(?)->addCustomTracer(?);', [$this->prefix('client'), $functionName]);
		}
	}

}
