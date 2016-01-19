<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treebank extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data['page_title'] = lang('public_treebanks');
		$data['treebanks'] = $this->treebank_model->get_public_treebanks();

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}

	public function show($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);

		$data['page_title'] = sprintf(lang('treebank_detail'), $title);
		$data['treebank'] = $treebank;
		$data['components'] = $this->component_model->get_components_by_treebank($treebank->id);

		$this->load->view('header', $data);
		$this->load->view('treebank_detail', $data);
		$this->load->view('footer');
	}

	public function delete($treebank_id)
	{
		$treebank = $this->treebank_model->get_treebank_by_id($treebank_id);
		$components = $this->component_model->get_components_by_treebank($treebank_id);
		foreach ($components as $component)
		{
			$this->delete_from_basex($component->basex_db);
		}
		$this->delete_from_basex(strtoupper($treebank->title . '_ID'));
		
		$treebank = $this->treebank_model->delete_treebank($treebank_id);

		$this->session->set_flashdata('message', lang('treebank_deleted'));
		redirect($this->agent->referrer(), 'refresh');
	}

	public function user($user_id)
	{
		$data['page_title'] = lang('my_treebanks');
		$data['treebanks'] = $this->treebank_model->get_treebanks_by_user($user_id);

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}

	private function delete_from_basex($db)
	{
		try
		{
			// Create session
			$session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

			// Delete database
			$session->send(sprintf("DROP DB %s", $db));
			echo $session->info();

			// Close session
			$session->close();
		} 
		catch (Exception $e) 
		{
			// Print exception
			echo $e->getMessage();
		}
	}
}
