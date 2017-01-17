<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Importrun_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	/**
	 * Starts an ImportRun for a Treebank by setting the processing field to TRUE, and cleaning the processed field.
	 * @param integer $treebank_id the ID of the Treebank
	 * @return integer             the new ID for the ImportRun
	 */
	public function start_importrun($treebank_id)
	{
		$this->treebank_model->update_treebank($treebank_id, array('processing' => TRUE, 'processed' => NULL));

		$this->db->insert('importruns', array('treebank_id' => $treebank_id));
		return $this->db->insert_id();
	}

	/**
	 * Ends an ImportRun for a Treebank by setting the processing field to FALSE, and filling the processed field.
	 * @param integer $importrun_id the ID of the ImportRun
	 * @param integer $treebank_id  the ID of the Treebank
	 */
	public function end_importrun($importrun_id, $treebank_id)
	{
		$processed = input_datetime();
		$this->treebank_model->update_treebank($treebank_id, array('processing' => FALSE, 'processed' => $processed));

		$this->db->where('id', $importrun_id);
		$this->db->update('importruns', array('time_ended' => $processed));
	}

	/**
	 * Retrieves the last ImportRun for a Treebank
	 * @param integer $treebank_id the ID of the Treebank
	 * @return array               the found ImportRuns	
	 */
	public function get_last_importrun_by_treebank($treebank_id)
	{
		$this->db->where('treebank_id', $treebank_id);
		$this->db->order_by('time_ended', 'DESC');
		return $this->db->get('importruns')->row();
	}

}
