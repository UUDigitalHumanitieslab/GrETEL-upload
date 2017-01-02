<?php
class Importlog_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function add_log($importrun_id, $level, $body, $filename = NULL, $linenumber = NULL)
	{
		$importlog = array(
			'importrun_id'	=> $importrun_id,
			'level'			=> $level,
			'body'			=> $body,
			'filename'		=> $filename,
			'linenumber'	=> $linenumber,
		);
		$this->db->insert('importlogs', $importlog);
		return $this->db->insert_id();
	}
}
