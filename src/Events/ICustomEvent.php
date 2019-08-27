<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\Events;

interface ICustomEvent
{

	public function getName() : string;



	/**
	 * @return array<string, string|int|float>
	 */
	public function getAttributes() : array;

}
