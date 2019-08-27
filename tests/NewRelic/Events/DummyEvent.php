<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\Events;

use Damejidlo\NewRelic\Events\ICustomEvent;



final class DummyEvent implements ICustomEvent
{

	/**
	 * @var array<string, string|float|int>
	 */
	private $attributes;



	/**
	 * @param array<string, string|float|int> $attributes
	 */
	public function __construct(array $attributes)
	{
		$this->attributes = $attributes;
	}



	public function getName() : string
	{
		return 'DummyEvent';
	}



	/**
	 * @return array<string, string|float|int>
	 */
	public function getAttributes() : array
	{
		return $this->attributes;
	}

}
