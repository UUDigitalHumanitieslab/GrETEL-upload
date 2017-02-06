<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Username extends CI_Migration
{

	public function up()
	{
		$username = array('type' => 'VARCHAR', 'constraint' => 200);
		if (!in_testing())
		{
			$username['unique'] = TRUE;
		}

		$fields = array(
			'username' => $username,
		);

		$this->dbforge->add_column('users', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('users', 'username');
	}

}
