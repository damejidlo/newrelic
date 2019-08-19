<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\TransactionName;

use Damejidlo\NewRelic\Utils\PathUtils;
use Nette\SmartObject;



class ConsoleTransactionNameProvider
{

	use SmartObject;

	/**
	 * @var PathUtils
	 */
	private $pathNormalizer;



	public function __construct(PathUtils $pathNormalizer)
	{
		$this->pathNormalizer = $pathNormalizer;
	}



	public function getTransactionName() : string
	{
		$transactionName = sprintf('$ %s', $this->pathNormalizer->normalize($_SERVER['argv'][0]));

		if (count($_SERVER['argv']) > 1) {
			$transactionName .= sprintf(' %s', implode(' ', array_slice($_SERVER['argv'], 1)));
		}

		return $transactionName;
	}

}
