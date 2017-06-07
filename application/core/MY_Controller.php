<?php

class MY_Controller extends CI_Controller
{

	protected $allowed_routes = array();

	public function __construct()
	{
		parent::__construct();

		$this->check_logged_in();
	}

	/**
	 * Checks whether non-authenticated users are allowed to access this page
	 * by comparing the current route to the $allowed_routes.
	 */
	private function check_logged_in()
	{
		if (!$this->session->userdata('logged_in'))
		{
			if (!in_array($this->router->fetch_method(), $this->allowed_routes))
			{
				redirect();
			}
		}
	}

}
