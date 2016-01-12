<?php
class Treebank_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function get_all_treebanks()
	{
		return $this->db->get('treebank')->result();
	}

	public function get_to_be_processed_treebanks()
	{
		$this->db->where('processed', NULL);
		return $this->db->get('treebank')->result();
	}

	public function get_treebanks_by_user($user_id)
	{
		$this->db->where('user_id', $user_id);
		return $this->db->get('treebank')->result();
	}

	public function get_treebank_by_title($title)
	{
		$this->db->where('title', $title);
		return $this->db->get('treebank')->row();
	}

	public function add_treebank($treebank)
	{
		$this->db->insert('treebank', $treebank);
		return $this->db->insert_id();
	}

	public function update_treebank($treebank_id, $treebank)
	{
		$this->db->where('id', $treebank_id);
		$this->db->update('treebank', $treebank);
	}

	/**
	* API calls
	*/
	public function get_public_treebanks()
	{
		$this->db->select(array('title', 'uploaded', 'processed'));
		$this->db->where('public', TRUE);
		$this->db->where('processed IS NOT NULL');
		return $this->db->get('treebank')->result();
	}
}
