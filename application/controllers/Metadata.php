<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Metadata extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Updates the facet for the given metadata field
	 */
	public function update_facet($metadata_id)
	{
		$metadata = $this->metadata_model->get_metadata_by_id($metadata_id);

		// Validate treebank
		if (!$this->validate_metadata())
		{
			// Show form again with error messages
			redirect('/treebank/detail/' . $metadata->treebank_id, 'refresh');
		}
		else 
		{
			// Update the metadata facet
			$this->metadata_model->update_metadata($metadata_id, $this->post_metadata());

			// Show the treebank detail data
			redirect('/treebank/detail/' . $metadata->treebank_id, 'refresh');
		}
	}
	
	/////////////////////////
	// Form handling
	/////////////////////////

	/**
	 * Validates the input.
	 * @return boolean Whether the validation has succeeded.
	 */
	private function validate_metadata()
	{
		$this->form_validation->set_rules('facet', lang('facet'), 'required');

		return $this->form_validation->run();
	}

	/**
	 * Posts the metadata data.
	 * @return array
	 */
	private function post_metadata()
	{
		return array(
			'facet'	=> $this->input->post('facet'),
		);
	}
}
