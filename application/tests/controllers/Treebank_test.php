<?php

class Treebank_test extends TestCase
{

	public function test_index()
	{
		$output = $this->request('GET', '/treebank/');
		$this->assertContains('<h2>Available treebanks</h2>', $output);
		
		
	}

}
