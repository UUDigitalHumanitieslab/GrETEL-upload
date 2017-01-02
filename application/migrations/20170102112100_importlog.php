<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_ImportLog extends CI_Migration 
{
	public function up()
	{
		// Create table for ImportRun
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'treebank_id' => array(
				'type' => 'INT',
			),
			'time_started' => array(
				'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			),
			'time_ended' => array(
				'type' => 'TIMESTAMP',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('treebank_id');
		$this->dbforge->create_table('importruns', FALSE, array('ENGINE' => 'InnoDB'));

		// Create table for ImportLog
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'importrun_id' => array(
				'type' => 'INT',
			),
			'time_logged' => array(
				'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			),
			'level' => array(
				'type' => 'ENUM("trace", "debug", "info", "warn", "error", "fatal")',
			),
			'body' => array(
				'type' => 'VARCHAR',
				'constraint' => '500',
			),
			'filename' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => TRUE,
			),
			'linenumber' => array(
				'type' => 'INT',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('importrun_id');
		$this->dbforge->create_table('importlogs', FALSE, array('ENGINE' => 'InnoDB'));

		$fields = array(
			'processing'	=> array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
		);
		$this->dbforge->add_column('treebanks', $fields);

		# Add FOREIGN KEYs via SQL.
		$this->db->query("ALTER TABLE `importruns`
			ADD FOREIGN KEY (`treebank_id`)
			REFERENCES `treebanks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
		$this->db->query("ALTER TABLE `importlogs`
			ADD FOREIGN KEY (`importrun_id`)
			REFERENCES `importruns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	}

	public function down()
	{
		$this->dbforge->drop_column('treebanks', 'processing');
		$this->dbforge->drop_table('importlogs');
		$this->dbforge->drop_table('importruns');
	}
}
