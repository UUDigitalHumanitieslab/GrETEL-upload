<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_ParseFlags extends CI_Migration {

	public function up()
	{
		$fields = array(
			'is_txt' 			=> array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
			'is_sent_tokenised' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
			'is_word_tokenised' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
			'has_labels' 		=> array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
		);

		$this->dbforge->add_column('treebanks', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('treebanks', 'is_txt');
		$this->dbforge->drop_column('treebanks', 'is_sent_tokenised');
		$this->dbforge->drop_column('treebanks', 'is_word_tokenised');
		$this->dbforge->drop_column('treebanks', 'has_labels');
	}
}
