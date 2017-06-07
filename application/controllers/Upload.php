<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends MY_Controller
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
		$this->allowed_routes = array();  // All routes should be blocked for non-authenticated Users

		parent::__construct();

		$config['upload_path'] = UPLOAD_DIR;
		$config['allowed_types'] = 'zip';
		$config['max_size'] = 16384;

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
			redirect('/treebank/user/' . current_user_id(), 'refresh');
		}
	}

	/////////////////////////
	// Form handling
	/////////////////////////

	/**
	 * Validates the form.
	 * @return bool Whether or not the validation has succeeded.
	 */
	private function validate_treebank()
	{
		$this->form_validation->set_rules('title', lang('title'), 'trim|required|alpha_dash|is_unique[treebanks.title]|max_length[200]');
		$this->form_validation->set_rules('treebank', lang('treebank'), 'callback_upload_treebank');
		$this->form_validation->set_rules('public', lang('public'), 'trim');
		$this->form_validation->set_rules('file_type', lang('file_type'), 'trim');
		$this->form_validation->set_rules('is_sent_tokenised', lang('is_sent_tokenised'), 'trim');
		$this->form_validation->set_rules('is_word_tokenised', lang('is_word_tokenised'), 'trim');
		$this->form_validation->set_rules('has_labels', lang('has_labels'), 'trim');

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
			'user_id' => current_user_id(),
			'title' => $this->input->post('title'),
			'filename' => $this->uploaded_treebank,
			'public' => $this->input->post('public') === '1',
			'file_type' => $this->input->post('file_type'),
			'is_sent_tokenised' => $this->input->post('is_sent_tokenised') === '1',
			'is_word_tokenised' => $this->input->post('is_word_tokenised') === '1',
			'has_labels' => $this->input->post('has_labels') === '1',
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
