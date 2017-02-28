<?php

class TestCase extends CIPHPUnitTestCase
{

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		$CI = &get_instance();

		$CI->load->database('test', FALSE, TRUE);

		$CI->load->library('migration');
		$CI->migration->current();

		$CI->load->library('Seeder');
		$CI->seeder->call('UserSeeder');
	}

}
