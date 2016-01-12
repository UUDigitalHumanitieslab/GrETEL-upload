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
		$data['page_title'] = lang('treebanks');
		$data['treebanks'] = $this->treebank_model->get_public_treebanks();

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}

	public function user($user_id)
	{
		$data['page_title'] = lang('treebanks_by_user');
		$data['treebanks'] = $this->treebank_model->get_treebanks_by_user($user_id);

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}
}
