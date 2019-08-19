<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

use Damejidlo\NewRelic\Utils\PathUtils;
use Nette\SmartObject;



class ConsoleApplicationModuleNameProvider implements IApplicationModuleNameProvider
{

	use SmartObject;

	/**
	 * @var PathUtils
	 */
	private $pathNormalizer;

	/**
	 * @var ModuleNameResolver
	 */
	private $moduleNameResolver;

	/**
	 * @var array<string, string>
	 */
	private $moduleNameByCommandPrefix;



	/**
	 * @param PathUtils $pathNormalizer
	 * @param ModuleNameResolver $moduleNameResolver
	 * @param array<string, string> $moduleNameByCommandPrefix
	 */
	public function __construct(PathUtils $pathNormalizer, ModuleNameResolver $moduleNameResolver, array $moduleNameByCommandPrefix)
	{
		$this->pathNormalizer = $pathNormalizer;
		$this->moduleNameResolver = $moduleNameResolver;
		$this->moduleNameByCommandPrefix = $moduleNameByCommandPrefix;
	}



	public function getModuleName() : string
	{
		$commandPath = $this->pathNormalizer->normalize($_SERVER['argv'][0]);
		$command = $commandPath . ' ' . implode(' ', array_slice($_SERVER['argv'], 1));
		return $this->moduleNameResolver->resolveModuleName($this->moduleNameByCommandPrefix, $command);
	}

}
