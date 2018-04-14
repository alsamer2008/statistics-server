<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Controllers;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\Views\Stats\StatsJsonView;

/**
 * Controller for displaying submitted statistics data.
 *
 * @method         \Joomla\StatsServer\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\WebApplication  $app              Application object
 */
class DisplayControllerGet extends AbstractController
{
	/**
	 * JSON view for displaying the statistics.
	 *
	 * @var  StatsJsonView
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView  $view  JSON view for displaying the statistics.
	 */
	public function __construct(StatsJsonView $view)
	{
		$this->view = $view;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		// Check if we are allowed to receive the raw data
		$authorizedRaw = $this->getInput()->server->getString('HTTP_JOOMLA_RAW', 'fail') === $this->getApplication()->get('stats.rawdata', false);

		// Check if a single data source is requested
		$source = $this->getInput()->getString('source', '');

		$this->view->isAuthorizedRaw($authorizedRaw);
		$this->view->setSource($source);

		$this->getApplication()->setBody($this->view->render());

		return true;
	}
}
