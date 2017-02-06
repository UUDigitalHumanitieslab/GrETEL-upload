<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Metadata extends CI_Migration
{

	public function up()
	{
		$engine = array();
		if (!in_testing())
		{
			$engine = array('ENGINE' => 'InnoDB');
		}

		$m_type = array('type' => 'ENUM("text", "int", "float", "date", "datetime")');
		$m_facet = array('type' => 'ENUM("checkbox", "dropdown", "slider", "date_range")');
		if (in_testing())
		{
			$m_type = array('type' => 'VARCHAR', 'constraint' => '200');
			$m_facet = array('type' => 'VARCHAR', 'constraint' => '200');
		}

		// Create table for Metadata
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'treebank_id' => array(
				'type' => 'INT',
			),
			'field' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
			),
			'type' => $m_type,
			'facet' => $m_facet,
			'min_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'max_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('treebank_id');
		$this->dbforge->add_key(array('treebank_id', 'field'));
		$this->dbforge->create_table('metadata', FALSE, $engine);

		# Add FOREIGN KEY via SQL
		if (!in_testing())
		{
			$this->db->query("ALTER TABLE `metadata`
				ADD FOREIGN KEY (`treebank_id`)
				REFERENCES `treebanks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
		}
	}

	public function down()
	{
		$this->dbforge->drop_table('metadata');
	}

}
