<?php
/**
 * @package     JTracker
 * @subpackage  CLI
 *
 * @copyright   Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CliApp\Application;

use Joomla\Application\AbstractCliApplication;
use Joomla\Input;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseDriver;

use CliApp\Command\TrackerCommand;

// Configure error reporting to maximum for CLI output.
error_reporting(-1);
ini_set('display_errors', 1);

/**
 * Simple Installer.
 *
 * @package     JTracker
 * @subpackage  CLI
 * @since       1.0
 */
class TrackerApplication extends AbstractCliApplication
{
	/**
	 * @var  DatabaseDriver
	 */
	private $database = null;

	/**
	 * Quiet mode - no output.
	 *
	 * @var bool
	 */
	private $quiet = false;

	/**
	 * Execute the application.
	 *
	 * @throws \RuntimeException
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->loadConfiguration();

		parent::execute();
	}

	/**
	 * Get a database driver object.
	 *
	 * @return DatabaseDriver
	 */
	public function getDatabase()
	{
		if (is_null($this->database))
		{
			return $this->createDatabase();
		}

		return $this->database;
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @throws \RuntimeException
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$args = $this->input->args;

		if (!$args)
		{
			$command = 'help';
			$action  = 'help';
		}
		else
		{
			$command = $args[0];

			$action = (isset($args[1])) ? $args[1] : $command;
		}

		$className = 'CliApp\\Command\\' . ucfirst($command) . '\\' . ucfirst($action);

		if (false == class_exists($className))
		{
			throw new \RuntimeException('Missing class: ' . $className);
		}

		if (false == method_exists($className, 'execute'))
		{
			throw new \RuntimeException(sprintf('Missing method %1$s::%2$s', $className, 'execute'));
		}

		/* @var TrackerCommand $class */
		$class = new $className($this);

		$class->execute();
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  TrackerApplication
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function out($text = '', $nl = true)
	{
		return ($this->quiet) ? $this : parent::out($text, $nl);
	}

	/**
	 * Load the application configuration.
	 *
	 * @throws \RuntimeException
	 *
	 * @return $this
	 */
	protected function loadConfiguration()
	{
		$path = realpath(__DIR__ . '/../../..') . '/etc/configuration.php';

		if (false == file_exists($path))
		{
			throw new \RuntimeException('Configuration missing: ' . $path);
		}

		include $path;

		$this->config->loadObject(new \JConfig);

		return $this;
	}

	/**
	 * Create an database object.
	 *
	 * @return  DatabaseDriver
	 *
	 * @see     DatabaseDriver
	 * @since   1.0
	 */
	protected function createDatabase()
	{
		$options = array(
			'driver'   => $this->get('dbtype'),
			'host'     => $this->get('host'),
			'user'     => $this->get('user'),
			'password' => $this->get('password'),
			'database' => $this->get('db'),
			'prefix'   => $this->get('dbprefix')
		);

		$database = DatabaseDriver::getInstance($options);

		$database->setDebug($this->get('debug'));

		$this->database = $database;

		return $database;
	}

	/**
	 * This is a useless legacy function.
	 *
	 * @todo remove
	 *
	 * @return string
	 */
	public function getUserStateFromRequest()
	{
		return '';
	}
}