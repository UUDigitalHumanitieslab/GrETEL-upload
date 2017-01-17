<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	/**
	 * Returns the User for the given ID
	 * @param integer $user_id the ID of the User
	 * @return User            the found User
	 */
	public function get_user_by_id($user_id)
	{
		$this->db->where('id', $user_id);
		return $this->db->get('users')->row();
	}

	/**
	 * Returns the User for the given username
	 * @param string $username the username of the User
	 * @return User            the found User
	 */
	public function get_user_by_username($username)
	{
		$this->db->where('username', $username);
		return $this->db->get('users')->row();
	}

	/**
	 * Creates a new User
	 * @param array $user the fields for the User
	 * @return integer    the new ID for the User
	 */
	public function create_user($user)
	{
		$this->db->insert('users', $user);
		return $this->db->insert_id();
	}

}
