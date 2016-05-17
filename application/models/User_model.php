<?php
class User_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function get_user_by_username($username)
	{
		$this->db->where('username', $username);
		return $this->db->get('users')->row();
	}

	public function create_user($user)
	{
		$this->db->insert('users', $user);
		return $this->db->insert_id();
	}
}
