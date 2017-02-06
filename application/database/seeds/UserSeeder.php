<?php

class UserSeeder extends Seeder
{

	public function run()
	{
		$this->db->truncate('users');

		$data = [
			'id' => 1,
			'email' => 'testing@test.nl',
			'username' => 'test',
		];
		$this->db->insert('users', $data);
	}

}
