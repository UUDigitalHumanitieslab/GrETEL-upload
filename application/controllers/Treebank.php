<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Treebank extends MY_Controller
{
    public function __construct()
    {
        $this->allowed_routes = array('index', 'show', 'detail');

        parent::__construct();
    }

    /**
     * Returns all public Treebanks (=public and processed).
     *
     * @return Loads the list view
     */
    public function index()
    {
        $data['page_title'] = lang('public_treebanks');
        $data['treebanks'] = $this->treebank_model->get_public_treebanks(current_user_id());

        $this->load->view('header', $data);
        $this->load->view('treebank_list', $data);
        $this->load->view('footer');
    }

    /**
     * Returns details for a Treebank.
     *
     * @param string $title the title of the Treebank
     *
     * @return Loads the detail view
     */
    public function show($title)
    {
        // If no title is provided, show a 404
        if (!isset($title)) {
            show_404();
        }

        $treebank = $this->treebank_model->get_treebank_by_title($title);

        // If this Treebank is private, only allow access to its owner.
        if (!$treebank->public && $treebank->user_id != current_user_id()) {
            show_error(lang('not_authorized'), 403);
        }

        $data['page_title'] = sprintf(lang('treebank_detail'), $title);
        $data['treebank'] = $treebank;
        $data['components'] = $this->component_model->get_components_by_treebank($treebank->id);
        $data['metadata'] = $this->metadata_model->get_metadata_by_treebank($treebank->id);
        $data['total_sentences'] = $this->component_model->get_sum($treebank->id, 'nr_sentences');
        $data['total_words'] = $this->component_model->get_sum($treebank->id, 'nr_words');

        $this->load->view('header', $data);
        $this->load->view('treebank_detail', $data);
        $this->load->view('footer');
    }

    /**
     * Redirect for the 'show' method above, using the ID instead of the title.
     *
     * @param int $treebank_id the ID of the Treebank
     */
    public function detail($treebank_id)
    {
        $treebank = $this->get_or_404($treebank_id);
        redirect('treebank/show/'.$treebank->title);
    }

    /**
     * Returns the ImportLogs from the most recent ImportRun for a Treebank.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return Loads the log view
     */
    public function log($treebank_id)
    {
        $treebank = $this->get_or_404($treebank_id);
        $this->check_is_owner($treebank->user_id);

        $importrun = $this->importrun_model->get_last_importrun_by_treebank($treebank_id);

        $data['page_title'] = sprintf(lang('treebank_log'), $treebank->title);
        $data['importlogs'] = $this->importlog_model->get_importlogs_by_importrun($importrun->id);

        $this->load->view('header', $data);
        $this->load->view('treebank_log', $data);
        $this->load->view('footer');
    }

    /**
     * Alters the accessibility of a Treebank (public <-> private).
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return Redirects to the previous page
     */
    public function change_access($treebank_id)
    {
        $treebank = $this->get_or_404($treebank_id);
        $this->check_is_owner($treebank->user_id);

        $t = array('public' => !$treebank->public);
        $this->treebank_model->update_treebank($treebank_id, $t);

        $this->session->set_flashdata('message', lang('treebank_access_modified'));
        redirect($this->agent->referrer(), 'refresh');
    }

    /**
     * Deletes a Treebank from both BaseX as well as the database.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return Redirects to the previous page
     */
    public function delete($treebank_id)
    {
        $treebank = $this->get_or_404($treebank_id);
        $this->check_is_owner($treebank->user_id);

        // Delete the treebank from BaseX
        $components = $this->component_model->get_components_by_treebank($treebank_id);
        foreach ($components as $component) {
            $this->basex->delete($component->basex_db);
        }
        $this->basex->delete(strtoupper($treebank->title.'_ID'));

        // Delete the treebank from the database
        $this->treebank_model->delete_treebank($treebank_id);

        // Return to the previous page
        $this->session->set_flashdata('message', lang('treebank_deleted'));
        redirect($this->agent->referrer(), 'refresh');
    }

    /**
     * Returns all Treebanks of the current User.
     * TODO: allow admins all access?
     *
     * @param int $user_id the ID of the User
     *
     * @return Loads the list view
     */
    public function user($user_id)
    {
        $this->check_is_owner($user_id);

        $data['page_title'] = lang('my_treebanks');
        $data['treebanks'] = $this->treebank_model->get_treebanks_by_user($user_id);

        $this->load->view('header', $data);
        $this->load->view('treebank_list', $data);
        $this->load->view('footer');
    }

    /**
     * Retrieves the Treebank by its ID, shows a 404 if no Treebank is found.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return The found Treebank
     */
    private function get_or_404($treebank_id)
    {
        $treebank = $this->treebank_model->get_treebank_by_id($treebank_id);

        if (!$treebank) {
            show_404();
        }

        return $treebank;
    }

    /**
     * Checks if the given User ID is the current User ID, if not, shows a 403.
     *
     * @param int $user_id the ID of the User
     */
    private function check_is_owner($user_id)
    {
        if ($user_id != current_user_id()) {
            show_error(lang('not_authorized'), 403);
        }
    }
}
