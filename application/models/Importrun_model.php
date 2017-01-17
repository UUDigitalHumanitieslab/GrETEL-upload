<?php

class Importrun_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function start_importrun($treebank_id)
	{
		$this->treebank_model->update_treebank($treebank_id, array('processing' => TRUE, 'processed' => NULL));

		$this->db->insert('importruns', array('treebank_id' => $treebank_id));
		return $this->db->insert_id();
	}

	public function end_importrun($importrun_id, $treebank_id)
	{
		$processed = input_datetime();
		$this->treebank_model->update_treebank($treebank_id, array('processing' => FALSE, 'processed' => $processed));

		$this->db->where('id', $importrun_id);
		$this->db->update('importruns', array('time_ended' => $processed));
	}

	public function get_last_importrun_by_treebank($treebank)
	{
		$this->db->where('treebank_id', $treebank->id);
		$this->db->order_by('time_ended', 'DESC');
		return $this->db->get('importruns')->row();
	}

}
