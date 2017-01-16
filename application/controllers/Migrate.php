<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('migration');
	}

	/**
	 * Migrates the database to the latest and greatest.
	 */
	public function index()
	{
		if (!is_cli())
		{
			echo 'This script can only be accessed via the command line.' . PHP_EOL;
			return;
		}

		if (!$this->migration->current())
		{
			show_error($this->migration->error_string());
		}
		else
		{
			echo 'Successfully ran pending migrations.' . PHP_EOL;
		}
	}

	/**
	 * Migrates the database to the given version identifier.
	 * @param  integer $id the version identifier
	 */
	public function version($id)
	{
		if (!is_cli())
		{
			echo 'This script can only be accessed via the command line.' . PHP_EOL;
			return;
		}

		if (!$this->migration->version($id))
		{
			show_error($this->migration->error_string());
		}
		else
		{
			echo 'Successfully migrated to version ' . $id . '.' . PHP_EOL;
		}
	}
}
