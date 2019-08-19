<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

interface IApplicationNameProvider
{

	public function getApplicationName() : string;

}
