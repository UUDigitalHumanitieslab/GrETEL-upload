<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Initial extends CI_Migration 
{
	public function up()
	{
		// Create table for Users
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'unique' => TRUE,
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('users', FALSE, array('ENGINE' => 'InnoDB'));

		// Create table for Treebanks
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'user_id' => array(
				'type' => 'INT',
			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
			),
			'filename' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => TRUE,
			),
			'public' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => '0',
			),
			'uploaded' => array(
				'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			),
			'processed' => array(
				'type' => 'TIMESTAMP',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('user_id');
		$this->dbforge->create_table('treebanks', FALSE, array('ENGINE' => 'InnoDB'));

		// Create table for Components
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'treebank_id' => array(
				'type' => 'INT',
			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
			),
			'slug' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
			),
			'basex_db' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
			),
			'nr_sentences' => array(
				'type' => 'INT',
			),
			'nr_words' => array(
				'type' => 'INT',
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('treebank_id');
		$this->dbforge->create_table('components', FALSE, array('ENGINE' => 'InnoDB'));

		# Add FOREIGN KEYs via SQL. 
		$this->db->query("ALTER TABLE `treebanks`
			ADD FOREIGN KEY (`user_id`)
			REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;");
		$this->db->query("ALTER TABLE `components`
			ADD FOREIGN KEY (`treebank_id`)
			REFERENCES `treebanks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	}

	public function down()
	{
		$this->dbforge->drop_table('components');
		$this->dbforge->drop_table('treebanks');
		$this->dbforge->drop_table('users');
	}
}
