<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Metadata_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	/**
	 * Retrieves a Metadata model using its ID
	 * @param  integer $metadata_id the ID of the Metadata
	 * @return Metadata_model       the found Metadata
	 */
	public function get_metadata_by_id($metadata_id)
	{
		$this->db->where('id', $metadata_id);
		return $this->db->get('metadata')->row();
	}

	/**
	 * Retrieves a Metadata model using the ID of the Treebank and its field value
	 * @param  integer $treebank_id the ID of the Treebank
	 * @param  integer $field       the field of the Metadata
	 * @return Metadata_model       the found Metadata
	 */
	public function get_metadata_by_treebank_field($treebank_id, $field)
	{
		$this->db->where('treebank_id', $treebank_id);
		$this->db->where('field', $field);
		return $this->db->get('metadata')->row();
	}

	/**
	 * Creates a new Metadata model
	 * @param array $metadata the fields for the Metadata
	 * @return integer        the new ID for the Metadata
	 */
	public function add_metadata($metadata)
	{
		$this->db->insert('metadata', $metadata);
		return $this->db->insert_id();
	}

	/**
	 * Updates a Metadata model
	 * @param  integer $metadata_id the ID of the Metadata
	 * @param  array $metadata      the fields for the Metadata
	 */
	public function update_metadata($metadata_id, $metadata)
	{
		$this->db->where('id', $metadata_id);
		$this->db->update('metadata', $metadata);
	}

	/**
	 * Updates the min/max value of a Metadata model, if necessary
	 * @param  integer $metadata_id the ID of the Metadata
	 * @param  string $value        the new value
	 * @return boolean              whether an update has been performed
	 */
	public function update_minmax($metadata_id, $value)
	{
		$result = FALSE;
		$metadata = $this->get_metadata_by_id($metadata_id);

		if (in_array($metadata->type, array('int', 'float', 'date', 'datetime')))
		{
			if ($metadata->min_value === NULL || $value < $metadata->min_value)
			{
				$metadata->min_value = $value;
				$this->update_metadata($metadata_id, $metadata);
				$result = TRUE;
			}
			else if ($metadata->max_value === NULL || $value > $metadata->max_value)
			{
				$metadata->max_value = $value;
				$this->update_metadata($metadata_id, $metadata);
				$result = TRUE;
			}
		}

		return $result;
	}

	/////////////////////////
	// API Calls
	/////////////////////////
	
	/**
	 * Retrieves the Metadata models for a Treebank
	 * @param  integer $treebank_id the ID of the Treebank
	 * @param  boolean $show_hidden whether or not to return hidden Metadata models
	 * @return array                the found Metadata
	 */
	public function get_metadata_by_treebank($treebank_id, $show_hidden = TRUE)
	{
		$this->db->where('treebank_id', $treebank_id);
		if (!$show_hidden)
		{
			$this->db->where('show', TRUE);
		}
		return $this->db->get('metadata')->result();
	}

	/**
	 * Retrieves the Metadata models for a Component
	 * @param  integer $component_id the ID of the Component
	 * @param  boolean $show_hidden  whether or not to return hidden Metadata models
	 * @return array                 the found Metadata
	 */
	public function get_metadata_by_component($component_id, $show_hidden = TRUE)
	{
		$component = $this->component_model->get_component_by_id($component_id);
		return $this->get_metadata_by_treebank($component->treebank_id, $show_hidden);
	}

}
