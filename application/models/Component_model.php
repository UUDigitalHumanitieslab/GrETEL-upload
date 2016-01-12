<?php
class Component_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function get_all_components()
	{
		return $this->db->get('component')->result();
	}

	public function add_component($component)
	{
		$this->db->insert('component', $component);
		return $this->db->insert_id();
	}

	public function update_component($component_id, $component)
	{
		$this->db->where('id', $component_id);
		$this->db->update('component', $component);
	}

	/**
	* API calls
	*/
	public function get_components_by_treebank($treebank_id)
	{
		$this->db->select(array('title', 'slug', 'nr_sentences', 'nr_words'));
		$this->db->where('treebank_id', $treebank_id);
		return $this->db->get('component')->result();
	}
}
