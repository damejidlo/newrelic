<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\Utils;

use Nette\SmartObject;
use Nette\Utils\Strings;



class PathUtils
{

	use SmartObject;

	/**
	 * @var string
	 */
	private $baseDir;

	/**
	 * @var string|NULL
	 */
	private $baseDirRealPath;



	public function __construct(string $baseDir = __DIR__ . '/../../../..')
	{
		$this->baseDir = $baseDir;
	}



	public function normalize(string $path) : string
	{
		$path = $this->realPath($path);
		$baseDir = $this->getBaseDirRealPath();

		if (Strings::startsWith($path, $baseDir)) {
			$path = Strings::substring($path, Strings::length($baseDir));
		}

		return $path;
	}



	private function getBaseDirRealPath() : string
	{
		if ($this->baseDirRealPath === NULL) {
			$this->baseDirRealPath = $this->realPath($this->baseDir) . '/';
		}

		return $this->baseDirRealPath;
	}



	private function realPath(string $path) : string
	{
		$realPath = realpath($path);
		if ($realPath === FALSE) {
			throw new \UnexpectedValueException("Could not determine real path of '$path'.");
		}

		return $realPath;
	}

}
