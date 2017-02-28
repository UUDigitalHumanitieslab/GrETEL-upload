<?php

class User_model_test extends TestCase
{

	public function test_get_user_by_id()
	{
		$CI = &get_instance();
		$user = $CI->user_model->get_user_by_id(1);

		$this->assertEquals('testing@test.nl', $user->email);
	}

	public function test_get_user_by_username()
	{
		$CI = &get_instance();
		$user = $CI->user_model->get_user_by_username('test');

		$this->assertEquals('testing@test.nl', $user->email);
	}

	public function test_create_user()
	{
		$CI = &get_instance();

		$user = array('email' => 'testing2@test.nl', 'username' => 'test2');
		$user_id = $CI->user_model->create_user($user);
		$u = $CI->user_model->get_user_by_id($user_id);
		$this->assertEquals('testing2@test.nl', $u->email);
	}

}
