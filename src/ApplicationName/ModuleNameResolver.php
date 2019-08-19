<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\ApplicationName;

use Nette\SmartObject;
use Nette\Utils\Strings;



class ModuleNameResolver
{

	use SmartObject;



	/**
	 * @param array<string, string> $moduleNameByIdentifierPrefix
	 * @param string $identifier
	 * @return string
	 */
	public function resolveModuleName(array $moduleNameByIdentifierPrefix, string $identifier) : string
	{
		krsort($moduleNameByIdentifierPrefix);

		foreach ($moduleNameByIdentifierPrefix as $identifierPrefix => $moduleName) {
			if (Strings::startsWith($identifier, $identifierPrefix)) {
				return $moduleName;
			}
		}

		return '';
	}

}
