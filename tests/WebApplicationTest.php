<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\StatsServer\WebApplication;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\WebApplication
 */
class WebApplicationTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	public function setUp()
	{
		parent::setUp();

		$this->backupServer = $_SERVER;
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		parent::tearDown();
	}

	/**
	 * @testdox The application executes correctly
	 *
	 * @covers  Joomla\StatsServer\WebApplication::doExecute
	 */
	public function testTheApplicationExecutesCorrectly()
	{
		// Mock a GET request
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$mockController = $this->getMockBuilder('Joomla\StatsServer\Controllers\DisplayControllerGet')
			->disableOriginalConstructor()
			->getMock();

		$mockController->expects($this->once())
			->method('execute')
			->willReturn(true);

		$mockRouter = $this->getMockBuilder('Joomla\StatsServer\Router')
			->disableOriginalConstructor()
			->getMock();

		$mockRouter->expects($this->once())
			->method('getController')
			->willReturn($mockController);

		(new WebApplication)
			->setRouter($mockRouter)
			->execute();
	}

	/**
	 * Data provider for testTheApplicationHandlesExceptionsCorrectly
	 *
	 * @return  array
	 */
	public function dataApplicationExceptions()
	{
		return [
			'401' => [401],
			'403' => [403],
			'404' => [404],
			'500' => [500],
		];
	}

	/**
	 * @testdox The application handles Exceptions correctly
	 *
	 * @param   integer  $code  The Exception code
	 *
	 * @covers  Joomla\StatsServer\WebApplication::doExecute
	 * @covers  Joomla\StatsServer\WebApplication::setErrorHeader
	 *
	 * @dataProvider dataApplicationExceptions
	 */
	public function testTheApplicationHandlesExceptionsCorrectly($code)
	{
		// Mock a GET request
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$mockController = $this->getMockBuilder('Joomla\StatsServer\Controllers\DisplayControllerGet')
			->disableOriginalConstructor()
			->getMock();

		$mockController->expects($this->once())
			->method('execute')
			->willThrowException(new \Exception('Test failure', $code));

		$mockRouter = $this->getMockBuilder('Joomla\StatsServer\Router')
			->disableOriginalConstructor()
			->getMock();

		$mockRouter->expects($this->once())
			->method('getController')
			->willReturn($mockController);

		$app = new WebApplication;
		$app->setRouter($mockRouter);

		// The execute method sends the response, which includes the body output; catch it in a buffer
		ob_start();
		$app->execute();
		ob_end_clean();

		// The status header should be first in the stack
		$statusHeader = $app->getHeaders()[0];

		$this->assertEquals($statusHeader['value'], $code, 'The Status header was not correctly set.');
	}

	/**
	 * @testdox The router is set to the application
	 *
	 * @covers  Joomla\StatsServer\WebApplication::setRouter
	 */
	public function testTheRouterIsSetToTheApplication()
	{
		$mockRouter = $this->getMockBuilder('Joomla\StatsServer\Router')
			->disableOriginalConstructor()
			->getMock();

		$app = new WebApplication;
		$app->setRouter($mockRouter);

		$this->assertAttributeSame($mockRouter, 'router', $app);
	}
}
