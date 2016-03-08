<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller 
{
	/**
	 * Temporary variable for the uploaded Treebank.
	 * @var File
	 */
	private $uploaded_treebank;

	/**
	 * - Configures the Upload library
	 * - Sets the correct error delimiters
	 */
	public function __construct()
	{
		parent::__construct();

		$config['upload_path']		= UPLOAD_DIR;
		$config['allowed_types']	= 'zip';
		$config['max_size']			= 16384;

		$this->load->library('upload', $config);

		$this->form_validation->set_error_delimiters('<label class="error">', '</label>');
	}

	/**
	 * Shows a view to upload a new Treebank.
	 * @return Loads the upload view.
	 */
	public function index()
	{
		$data['page_title'] = lang('upload_treebank');
		$data['action'] = 'upload/submit';

		$this->load->view('header', $data);
		$this->load->view('treebank_upload', $data);
		$this->load->view('footer');
	}

	/**
	 * Handles submission of the upload.
	 * @return On success: redirects to the previous page.
	 */
	public function submit()
	{
		// Validate treebank
		if (!$this->validate_treebank())
		{
			// Show form again with error messages
			$this->index();
		}
		else 
		{
			// Add treebank to database
			$treebank = $this->post_treebank();
			$this->treebank_model->add_treebank($treebank);

			// Show my treebanks
			$this->session->set_flashdata('message', lang('upload_success'));
			redirect('/treebank/user/1', 'refresh');
		}
	}
	
	/////////////////////////
	// Form handling
	/////////////////////////

	/**
	 * Validates the input.
	 * @return boolean Whether the validation has succeeded.
	 */
	private function validate_treebank()
	{
		$this->form_validation->set_rules('title', lang('title'), 'trim|required|is_unique[treebanks.title]|max_length[200]');
		$this->form_validation->set_rules('treebank', lang('treebank'), 'callback_upload_treebank');
		$this->form_validation->set_rules('public', lang('public'), '');

		return $this->form_validation->run();
	}

	/**
	 * Posts the treebank data.
	 * TODO: generate a slug here.
	 * @return array
	 */
	private function post_treebank()
	{
		return array(
			'user_id'	=> 1,	// TODO: set to current user
			'title'		=> $this->input->post('title'),
			'filename'	=> $this->uploaded_treebank,
			'public'	=> is_array($this->input->post('public')),
		);
	}

	/////////////////////////
	// Callbacks
	/////////////////////////

	/**
	 * Uploads a Treebank file to the specified upload directory.
	 * @return boolean Whether the upload has succeeded.
	 */
	public function upload_treebank()
	{
		if (!$this->upload->do_upload('treebank'))
		{
			$this->form_validation->set_message('upload_treebank', $this->upload->display_errors());
			return FALSE;
		}
		else
		{
			$data = $this->upload->data();
			if ($data['file_name']) 
			{
				$this->uploaded_treebank = $data['file_name'];
			}
			return TRUE;
		}
	}
}
