<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Allows users to login with their e-mail address and password.
	 * If there is already a logged in user, redirects to the upload view.
	 * @return Loads the base login view.
	 */
	public function index()
	{
		if (current_user_id())
		{
			redirect('upload');
		}

		$data['page_title'] = lang('login');
		$data['action'] = 'login/submit';

		$this->load->view('header', $data);
		$this->load->view('login', $data);
		$this->load->view('footer');
	}

	/**
	 * Validates the given e-mail address and password against the LDAP database. 
	 * If correct, the session cookie will be created.
	 * If not correct, register an unsuccesful login for this IP and return to the login page.
	 * @return Loads the success view.
	 */
	public function submit()
	{
		if (!$this->validate())
		{
			$this->index();
		}
		else
		{
			$username = $this->input->post('username');
			$user = $this->user_model->get_user_by_username($username);

			// If we can't find the User, create a new one
			if (!$user)
			{
				$email = $this->ldap->get_user_attributes($username, array('mail'))['mail'][0];

				$user = array(
					'username'	=> $username,
					'email'		=> $email,
				);
				$user_id = $this->user_model->create_user($user);
			}
			// Else, retrieve the ID from the found User
			else
			{
				$user_id = $user->id;
			}

			// Set the userdata, and redirect to the upload page
			$this->session->set_userdata(array(
				'logged_in'	=> TRUE,
				'user_id'	=> $user_id,
			));
			redirect('upload');
		}
	}

	public function guest()
	{
		$user = $this->user_model->get_user_by_username(GUEST_USERNAME);

		// If we can't find the User, create a new one
		if (!$user)
		{
			$user = array(
				'username'	=> GUEST_USERNAME,
				'email'		=> GUEST_EMAIL,
			);
			$user_id = $this->user_model->create_user($user);
		}
		// Else, retrieve the ID from the found User
		else
		{
			$user_id = $user->id;
		}

		// Set the userdata, and redirect to the upload page
		$this->session->set_userdata(array(
			'logged_in' => TRUE,
			'user_id'   => $user_id,
		));
		redirect('upload');
	}
	
	/////////////////////////
	// Form handling
	/////////////////////////

	private function validate()
	{
		$this->form_validation->set_rules('username', lang('username'), 'required|callback_password_check');
		$this->form_validation->set_rules('password', lang('password'), 'required');

		return $this->form_validation->run();
	}

	public function password_check($username)
	{
		$password = $this->input->post('password');

		if (!$this->ldap->check_credentials($username, $password))
		{
			$this->form_validation->set_message('password_check', lang('invalid_credentials'));
			// TODO: Mark this as a failed login for this IP.
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}
