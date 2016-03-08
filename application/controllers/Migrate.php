<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('migration');
	}

	public function index()
	{
		if (!is_cli())
		{
			echo 'This script can only be accessed via the command line.' . PHP_EOL;
			return;
		}

		if ($this->migration->current() === FALSE)
		{
			show_error($this->migration->error_string());
		}
		else
		{
			echo 'Successfully ran migrations.' . PHP_EOL;
		}
	}
}
