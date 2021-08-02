<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends MY_Controller
{

	public function __construct()
	{
		$this->allowed_routes = array();  // All routes should be blocked for non-authenticated Users

		parent::__construct();
	}

	/**
	 * Destroys the current session.
	 * @return void redirects to the login view
	 */
	public function index()
	{
		$this->user_status->logout();
		redirect('login');
	}

}
