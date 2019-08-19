<?php
declare(strict_types = 1);

namespace DamejidloTests\NewRelic\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\NewRelic\Client;
use Damejidlo\NewRelic\NewRelicProfilingListener;
use DamejidloTests\DjTestCase;
use DamejidloTests\FunctionMocks;
use Nette\Configurator;
use Tester\Assert;



/**
 * @testCase
 */
class NewRelicExtensionTest extends DjTestCase
{

	/**
	 * @var Configurator
	 */
	private $configurator;



	public function testWebRequest() : void
	{
		FunctionMocks::expect('newrelic_set_appname', ['testApplication/Api']);
		FunctionMocks::expect('newrelic_disable_autorum', []);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\UI\Presenter::createRequest']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\UI\Presenter::run']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Nette\Application\Responses\TextResponse::send']);
		FunctionMocks::expect('newrelic_add_custom_tracer', ['Doctrine\ORM\EntityManager::flush']);

		$this->configurator->addConfig(__DIR__ . '/fixtures/config.neon');
		$container = $this->configurator->createContainer();

		Assert::type(Client::class, $container->getService('newrelic.client'));
		Assert::type(NewRelicProfilingListener::class, $container->getService('newrelic.profilingListener'));
	}



	public function testConsoleRequest() : void
	{
		FunctionMocks::expect('newrelic_set_appname', ['testApplication/Console']);
		FunctionMocks::expect('newrelic_background_job', [TRUE]);
		FunctionMocks::expect('newrelic_disable_autorum', []);

		$this->configurator->addConfig(__DIR__ . '/fixtures/config.console.neon');
		$container = $this->configurator->createContainer();

		Assert::type(Client::class, $container->getService('newrelic.client'));
		Assert::type(NewRelicProfilingListener::class, $container->getService('newrelic.profilingListener'));
	}



	protected function setUp() : void
	{
		parent::setUp();

		$this->configurator = new Configurator();
		$this->configurator->setTempDirectory(TEMP_DIR);
		$this->configurator->setDebugMode(FALSE);
	}

}



(new NewRelicExtensionTest())->run();
