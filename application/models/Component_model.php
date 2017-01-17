<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Component_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	/**
	 * Retrieves all Components
	 * @return array the found Components
	 */
	public function get_all_components()
	{
		return $this->db->get('components')->result();
	}

	/**
	 * Retrieves a Component using its ID
	 * @param integer $component_id the ID of the Component
	 * @return Component_model      the found Component
	 */
	public function get_component_by_id($component_id)
	{
		$this->db->where('id', $component_id);
		return $this->db->get('components')->row();
	}

	/**
	 * Retrieves a Component using the ID of the Treebank and its title
	 * @param integer $treebank_id the ID of the Treebank
	 * @param string $title        the title of the Component
	 * @return Component_model     the found Component
	 */
	public function get_component_by_treebank_title($treebank_id, $title)
	{
		$this->db->where('treebank_id', $treebank_id);
		$this->db->where('title', $title);
		return $this->db->get('components')->row();
	}

	/**
	 * Creates a new Component
	 * @param array $component the fields for the Component
	 * @return integer         the new ID for the Component
	 */
	public function add_component($component)
	{
		$this->db->insert('components', $component);
		return $this->db->insert_id();
	}

	/**
	 * Updates a Component
	 * @param integer $component_id the ID of the Component
	 * @param array $component      the updated fields for the Component
	 */
	public function update_component($component_id, $component)
	{
		$this->db->where('id', $component_id);
		$this->db->update('components', $component);
	}

	/**
	 * Returns the sum for the given column
	 * @param integer $treebank_id the ID of the Treebank
	 * @param string $column       the column to calculate the sum for
	 * @return string              the sum
	 */
	public function get_sum($treebank_id, $column)
	{
		$this->db->select_sum($column, 'total');
		$this->db->where('treebank_id', $treebank_id);
		return $this->db->get('components')->row()->total;
	}

	/////////////////////////
	// API Calls
	/////////////////////////

	/**
	 * Retrieves all Components for a Treebank
	 * @param integer $treebank_id the ID of the Treebank
	 * @return array               the found Components
	 */
	public function get_components_by_treebank($treebank_id)
	{
		$this->db->select(array('slug', 'title', 'basex_db', 'nr_sentences', 'nr_words'));
		$this->db->where('treebank_id', $treebank_id);
		return $this->db->get('components')->result();
	}

}
