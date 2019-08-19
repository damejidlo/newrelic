<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

interface IApplicationModuleNameProvider
{

	public function getModuleName() : string;

}
