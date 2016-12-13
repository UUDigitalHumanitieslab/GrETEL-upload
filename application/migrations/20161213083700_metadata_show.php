<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Metadata_Show extends CI_Migration
{
	public function up()
	{
		$fields = array(
			'show'		=> array('type' => 'TINYINT', 'constraint' => 1, 'null' => FALSE, 'default' => 1),
		);

		$this->dbforge->add_column('metadata', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('metadata', 'show');
	}
}
