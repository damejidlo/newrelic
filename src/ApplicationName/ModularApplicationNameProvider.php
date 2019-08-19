<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

use Nette\SmartObject;



class ModularApplicationNameProvider implements IApplicationNameProvider
{

	use SmartObject;

	/**
	 * @var IApplicationNameProvider
	 */
	private $applicationNameProvider;

	/**
	 * @var IApplicationModuleNameProvider
	 */
	private $moduleNameProvider;



	public function __construct(IApplicationNameProvider $applicationNameProvider, IApplicationModuleNameProvider $moduleNameProvider)
	{
		$this->applicationNameProvider = $applicationNameProvider;
		$this->moduleNameProvider = $moduleNameProvider;
	}



	public function getApplicationName() : string
	{
		$applicationName = $this->applicationNameProvider->getApplicationName();
		$moduleName = $this->moduleNameProvider->getModuleName();

		if ($moduleName !== '') {
			$applicationName = "$applicationName/$moduleName";
		}

		return $applicationName;
	}

}
