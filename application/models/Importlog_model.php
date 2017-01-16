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
	
	public function get_importlogs_by_importrun($importrun_id)
	{
		$this->db->where('importrun_id', $importrun_id);

		// In production, don't show trace/debug level logs
		if (!in_development())
		{
			$this->db->where_not_in('level', array(LogLevel::Trace, LogLevel::Debug));
		}

		$this->db->order_by('time_logged', 'ASC');
		return $this->db->get('importlogs')->result();
	}
}
