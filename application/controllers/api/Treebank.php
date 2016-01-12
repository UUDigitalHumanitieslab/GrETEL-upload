<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Treebank extends REST_Controller 
{
	public function index_get()
	{
		$this->response($this->treebank_model->get_public_treebanks());
	}

	public function show_get($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);
		$this->response($this->component_model->get_components_by_treebank($treebank->id));
	}

	public function user_get($user_id)
	{
		$this->response($this->treebank_model->get_treebanks_by_user($user_id));
	}
}
