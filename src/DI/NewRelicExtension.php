<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\DI;

use Damejidlo\NewRelic\Client;
use Damejidlo\NewRelic\NewRelicProfilingListener;
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
		'autorum' => FALSE,
	];



	public function loadConfiguration() : void
	{
		$config = $this->validateConfig($this->defaults);
		Validators::assert($config['applicationName'], 'string:1..');
		Validators::assert($config['autorum'], 'bool');

		$containerBuilder = $this->getContainerBuilder();

		$containerBuilder->addDefinition($this->prefix('client'))
			->setType(Client::class);

		$containerBuilder->addDefinition($this->prefix('profilingListener'))
			->setType(NewRelicProfilingListener::class)
			->setFactory(NewRelicProfilingListener::class)
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

		if (! (bool) $config['autorum']) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody('$this->getService(?)->disableAutorum();', [$this->prefix('client')]);
		}
	}

}
