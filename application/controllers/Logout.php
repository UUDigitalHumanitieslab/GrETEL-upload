<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Destroys the current sessions and redirects to the login view.
	 */
	public function index()
	{
		$this->session->sess_destroy();
		redirect('login');
	}
}
