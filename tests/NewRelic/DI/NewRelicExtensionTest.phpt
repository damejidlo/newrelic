<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\Client;
use Damejidlo\NewRelic\NewRelicProfilingListener;
use DamejidloTests\DjTestCase;
use DamejidloTests\FunctionMocks;
use Nette\Configurator;
use Nette\DI\Container;
use Tester\Assert;



/**
 * @testCase
 */
class NewRelicExtensionTest extends DjTestCase
{

	/**
	 * @var Container
	 */
	private $container;



	public function testServices() : void
	{
		Assert::type(Client::class, $this->container->getService('newrelic.client'));
		Assert::type(NewRelicProfilingListener::class, $this->container->getService('newrelic.profilingListener'));
	}



	protected function setUp() : void
	{
		parent::setUp();

		FunctionMocks::expect('newrelic_disable_autorum', []);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\UI\Presenter::createRequest']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\UI\Presenter::run']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\Responses\TextResponse::send']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Doctrine\ORM\EntityManager::flush']);

		$configurator = new Configurator();
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->setDebugMode(FALSE);
		$configurator->addConfig(__DIR__ . '/fixtures/config.neon');
		$this->container = $configurator->createContainer();
	}

}



(new NewRelicExtensionTest())->run();
