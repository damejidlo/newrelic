<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\ApplicationName;

use Damejidlo\NewRelic\ApplicationName\IApplicationModuleNameProvider;



class DummyApplicationModuleNameProvider implements IApplicationModuleNameProvider
{

	/**
	 * @var string
	 */
	private $moduleName;



	public function __construct(string $moduleName)
	{
		$this->moduleName = $moduleName;
	}



	public function getModuleName() : string
	{
		return $this->moduleName;
	}

}
