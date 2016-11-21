<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Username extends CI_Migration {

	public function up()
	{
		$fields = array(
			'username' 			=> array('type' => 'VARCHAR', 'constraint' => 200, 'unique' => TRUE),
		);

		$this->dbforge->add_column('users', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('users', 'username');
	}
}
