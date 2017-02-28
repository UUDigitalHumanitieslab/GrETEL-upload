<?php

class Upload_test extends TestCase
{

	public function test_index()
	{
		// By default, redirect to login page
		$this->request('GET', '/upload/');
		$this->assertResponseCode(302);

		// If logged in, show a page to upload the treebank
		$this->request('GET', '/login/guest/');
		$output = $this->request('GET', '/upload/');
		$this->assertContains('<h2>Upload your treebank</h2>', $output);
		$this->assertContains('<input type="file" name="treebank"', $output);
	}

	public function test_upload()
	{
		$CI = &get_instance();
		$CI->load->helper('string');

		$this->request('GET', '/login/guest/');
		
		$treebank_title = random_string();
		$post = array(
			'user_id' => current_user_id(),
			'title' => $treebank_title,
			'public' => TRUE,
			'is_txt' => TRUE,
			'is_sent_tokenised' => TRUE,
			'is_word_tokenised' => FALSE,
			'has_labels' => FALSE,
		);

		$filename = 'testcorpus.zip';
		$filepath = APPPATH . 'tests/fixtures/' . $filename;
		$files = array(
			'treebank' => array(
				'name' => $filename,
				'type' => 'application/zip',
				'tmp_name' => $filepath,
			),
		);
		$this->request->setFiles($files);

		$this->request('POST', 'upload/submit', $post);
		$this->assertResponseCode(200);
		
		/*
		$CI = &get_instance();
		$treebank = $CI->treebank_model->get_treebank_by_title($treebank_title);
		$this->assertTrue((bool) $treebank->public);
		$this->assertEquals($filename, $treebank->filename);
		 */
	}

}
