<?php
declare(strict_types = 1);

namespace Damejidlo\NewRelic\TransactionName;

use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\SmartObject;
use Nette\Utils\Strings;



class NetteWebTransactionNameProvider
{

	use SmartObject;



	public function getTransactionName(Request $request) : string
	{
		$transactionName = $request->getPresenterName();

		$parameters = $request->getParameters() + $request->getPost();

		if (isset($parameters[Presenter::ACTION_KEY])) {
			$transactionName .= sprintf(':%s', $parameters[Presenter::ACTION_KEY]);
		}

		if (isset($parameters[Presenter::SIGNAL_KEY])) {
			$transactionName .= sprintf('?signal=%s', Strings::replace($parameters[Presenter::SIGNAL_KEY], '~[0-9]+~', '*'));
		}

		return $transactionName;
	}

}
