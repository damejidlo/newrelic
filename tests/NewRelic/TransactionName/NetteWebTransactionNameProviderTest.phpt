<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\TransactionName;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\TransactionName\NetteWebTransactionNameProvider;
use DamejidloTests\DjTestCase;
use Nette\Application\Request;
use Tester\Assert;



/**
 * @testCase
 */
class NetteWebTransactionNameProviderTest extends DjTestCase
{

	/**
	 * @dataProvider getDataForApplicationName
	 * @param Request $request
	 * @param string $expectedName
	 */
	public function testModuleName(Request $request, string $expectedName) : void
	{
		$netteWebTransactionNameProvider = new NetteWebTransactionNameProvider();

		Assert::same($netteWebTransactionNameProvider->getTransactionName($request), $expectedName);
	}



	/**
	 * @return mixed[]
	 */
	protected function getDataForApplicationName() : array
	{
		return [
			[
				'request' => new Request('Module:Presenter'),
				'expectedName' => 'Module:Presenter',
			],
			[
				'request' => new Request('Module:Presenter', 'GET', ['action' => 'default']),
				'expectedName' => 'Module:Presenter:default',
			],
			[
				'request' => new Request('Module:Presenter', 'GET', ['action' => 'default', 'do' => 'component-42-subcomponent-signal']),
				'expectedName' => 'Module:Presenter:default?signal=component-*-subcomponent-signal',
			],
			[
				'request' => new Request('Module:Presenter', 'POST', ['do' => 'form-submit']),
				'expectedName' => 'Module:Presenter?signal=form-submit',
			],
		];
	}

}



(new NetteWebTransactionNameProviderTest())->run();
