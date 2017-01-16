<?php
class Treebank_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function get_all_treebanks()
	{
		return $this->db->get('treebanks')->result();
	}

	public function get_to_be_processed_treebanks()
	{
		$this->db->where('processed', NULL);
		$this->db->where('processing', FALSE);
		return $this->db->get('treebanks')->result();
	}

	public function get_treebank_by_id($treebank_id)
	{
		$this->db->where('id', $treebank_id);
		return $this->db->get('treebanks')->row();
	}

	public function get_treebank_by_title($title)
	{
		$this->db->where('title', $title);
		return $this->db->get('treebanks')->row();
	}

	public function add_treebank($treebank)
	{
		$this->db->insert('treebanks', $treebank);
		return $this->db->insert_id();
	}

	public function update_treebank($treebank_id, $treebank)
	{
		$this->db->where('id', $treebank_id);
		$this->db->update('treebanks', $treebank);
	}
	
	public function delete_treebank($treebank_id)
	{
		$this->db->delete('treebanks', array('id' => $treebank_id));
	}

	/////////////////////////
	// API Calls
	/////////////////////////

	public function get_api_treebanks()
	{
		$this->db->select(array('treebanks.id', 'treebanks.title', 'users.id AS user_id', 'users.email', 'treebanks.uploaded', 'treebanks.processed', 'treebanks.public'));
		$this->db->from('treebanks');
		$this->db->join('users', 'users.id = treebanks.user_id');
		return $this->db->get()->result();
	}

	public function get_public_treebanks($user_id = NULL)
	{
		$this->db->where('processed IS NOT NULL');
		$this->db->group_start();
		$this->db->where('public', TRUE);
		if ($user_id) $this->db->or_where('user_id', $user_id);
		$this->db->group_end();
		return $this->get_api_treebanks();
	}

	public function get_treebanks_by_user($user_id)
	{
		$this->db->where('user_id', $user_id);
		return $this->get_api_treebanks();
	}
}
