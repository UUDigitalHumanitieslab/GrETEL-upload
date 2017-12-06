<?php

class Treebank_test extends TestCase
{
    public function test_index()
    {
        $output = $this->request('GET', '/treebank/');
        $this->assertContains('<h2>Available treebanks</h2>', $output);
    }

    public function test_show()
    {
        $this->request('GET', '/treebank/show/title');
        $this->assertResponseCode(200);
    }

    public function test_detail()
    {
        $this->request('GET', '/treebank/detail/42');
        $this->assertResponseCode(404);
    }
}
