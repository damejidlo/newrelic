<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

use Nette\SmartObject;



class SimpleApplicationNameProvider implements IApplicationNameProvider
{

	use SmartObject;

	/**
	 * @var string
	 */
	private $applicationName;



	public function __construct(string $applicationName)
	{
		$this->applicationName = $applicationName;
	}



	public function getApplicationName() : string
	{
		return $this->applicationName;
	}

}
