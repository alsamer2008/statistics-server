<?php

namespace Stats;

use Joomla\Application\AbstractWebApplication;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Web application for the stats server
 *
 * @since  1.0
 */
class WebApplication extends AbstractWebApplication
{
	/**
	 * Application analytics object.
	 *
	 * @var    Analytics
	 * @since  1.0
	 */
	private $analytics;

	/**
	 * Response mime type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $mimeType = 'application/json';

	/**
	 * Application router.
	 *
	 * @var    Router
	 * @since  1.0
	 */
	private $router;

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		// On a GET request to the live domain, submit analytics data
		if ($this->input->getMethod() === 'GET'
			&& strpos($this->input->server->getString('HTTP_HOST', ''), 'developer.joomla.org') === 0
			&& $this->analytics)
		{
			$this->analytics->setAsyncRequest(true)
				->setProtocolVersion('1')
				->setTrackingId('UA-544070-16')
				->setDocumentPath($this->get('uri.route'))
				->setIpOverride($this->input->server->getString('REMOTE_ADDR'))
				->setUserAgentOverride($this->input->server->getString('HTTP_USER_AGENT'));

			// Don't allow sending Analytics data to cause a failure
			try
			{
				$this->analytics->sendPageview();
			}
			catch (\Exception $e)
			{
				// Log the error for reference
				$this->getLogger()->error(
					'Error sending analytics data.',
					['exception' => $e]
				);
			}
		}

		try
		{
			$this->router->getController($this->get('uri.route'))->execute();
		}
		catch (\Exception $e)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Uncaught Exception of type %s caught.', get_class($e)),
				['exception' => $e]
			);

			$this->setErrorHeader($e);

			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];

			$this->setBody(json_encode($data));
		}
	}

	/**
	 * Set the application's analytics object.
	 *
	 * @param   Analytics  $analytics  Analytics object to set.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setAnalytics(Analytics $analytics)
	{
		$this->analytics = $analytics;

		return $this;
	}

	/**
	 * Set the HTTP Response Header for error conditions.
	 *
	 * @param   \Exception  $exception  The Exception object to process.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setErrorHeader(\Exception $exception)
	{
		switch ($exception->getCode())
		{
			case 401:
				$this->setHeader('HTTP/1.1 401 Unauthorized', 401, true);

				break;

			case 403:
				$this->setHeader('HTTP/1.1 403 Forbidden', 403, true);

				break;

			case 404:
				$this->setHeader('HTTP/1.1 404 Not Found', 404, true);

				break;

			case 500:
			default:
				$this->setHeader('HTTP/1.1 500 Internal Server Error', 500, true);

				break;
		}
	}

	/**
	 * Set the application's router.
	 *
	 * @param   Router  $router  Router object to set.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}
}
