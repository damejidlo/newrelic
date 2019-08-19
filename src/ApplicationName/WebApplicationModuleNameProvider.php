<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

use Nette\Http\IRequest;
use Nette\SmartObject;



class WebApplicationModuleNameProvider implements IApplicationModuleNameProvider
{

	use SmartObject;

	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @var ModuleNameResolver
	 */
	private $moduleNameResolver;

	/**
	 * @var array<string, string>
	 */
	private $moduleNameByPathPrefix;



	/**
	 * @param IRequest $request
	 * @param ModuleNameResolver $moduleNameResolver
	 * @param array<string, string> $moduleNameByPathPrefix
	 */
	public function __construct(IRequest $request, ModuleNameResolver $moduleNameResolver, array $moduleNameByPathPrefix)
	{
		$this->request = $request;
		$this->moduleNameResolver = $moduleNameResolver;
		$this->moduleNameByPathPrefix = $moduleNameByPathPrefix;
	}



	public function getModuleName() : string
	{
		$path = '/' . ltrim($this->request->getUrl()->getPathInfo(), '/');
		return $this->moduleNameResolver->resolveModuleName($this->moduleNameByPathPrefix, $path);
	}

}
