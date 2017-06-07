<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Component extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns the metadata of a Component, given its title.
	 * @param  integer $treebank_id The id of the Treebank.
	 * @param  string $title The title of the Component.
	 * @return JSON response.
	 */
	public function metadata_get($treebank_id, $title)
	{
		$component = $this->component_model->get_component_by_treebank_title($treebank_id, $title);

		if (!$component)
		{
			$this->response();
		}

		$this->response($this->metadata_model->get_metadata_by_component($component->id, FALSE));
	}

}
