<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Importlog_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	/**
	 * Creates a new ImportLog 
	 * @param integer $importrun_id the ID of the ImportRun
	 * @param string $level         the LogLevel
	 * @param string $body          the log body
	 * @param string $filename      the filename (if applicable)
	 * @param string $linenumber    the line number in the filename (if applicable)
	 * @return integer              the new ID for the ImportLog
	 */
	public function add_log($importrun_id, $level, $body, $filename = NULL, $linenumber = NULL)
	{
		$importlog = array(
			'importrun_id' => $importrun_id,
			'level' => $level,
			'body' => $body,
			'filename' => $filename,
			'linenumber' => $linenumber,
		);
		$this->db->insert('importlogs', $importlog);
		return $this->db->insert_id();
	}

	/**
	 * Retrieves all ImportLogs for an ImportRun
	 * @param integer $importrun_id the ID of the ImportRun
	 * @return array                the found ImportLogs
	 */
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
