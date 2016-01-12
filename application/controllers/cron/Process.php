<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends CI_Controller 
{
	public function index()
	{
		if (!is_cli())
		{
			echo 'This script can only be accessed via the command line' . PHP_EOL;
			return;
		}

		$treebanks = $this->treebank_model->get_to_be_processed_treebanks();
		foreach ($treebanks as $treebank)
		{
			$zip = new ZipArchive;
			$res = $zip->open(UPLOAD_DIR . $treebank->filename);
			if ($res === TRUE)
			{
				$root_dir = UPLOAD_DIR . substr($treebank->filename, 0, -4);
				$zip->extractTo($root_dir);
				$zip->close();

				$this->treebank_model->update_treebank($treebank->id, array('processed' => input_datetime()));

				foreach(glob($root_dir . '/*', GLOB_ONLYDIR) as $dir)
				{
					$title = basename($dir);
					$basex_db = strtoupper($treebank->title . '_ID_' . $title);

					$component = array(
						'treebank_id' 	=> $treebank->id,
						'title'			=> $title,
						'slug'			=> $title,
						'basex_db'		=> $basex_db);
					$component_id = $this->component_model->add_component($component);

					$this->merge_xml_files($dir, $component_id);
					$this->upload_to_basex($basex_db, $dir . '/total.xml');
				}

				echo 'Processed!';
			}
			else
			{
				echo 'File not found.';
			}
		}
	}

	private function merge_xml_files($dir, $component_id)
	{
		$nr_sentences = 0;
		$nr_words = 0;

		$treebank_xml = new DOMDocument();
		$treebank_xml->loadXML('<treebank/>');

		foreach(glob($dir . '/*.xml') as $file)
		{
			$file_xml = new DOMDocument();
			$file_xml->load($file);

			// Set the id attribute as the filename in the root element
			$file_xml->documentElement->setAttribute('id', basename($file));

			$xp = new DOMXPath($file_xml);
			$nr_sentences += $xp->evaluate('count(//sentence)');
			$nr_words += $xp->evaluate('count(//node/node)');	// TODO: hoe tel je dit netjes?

			// Attach the document to the original folder
			$node = $treebank_xml->importNode($file_xml->documentElement, TRUE);
			$treebank_xml->documentElement->appendChild($node);
		}

		$c = array(
			'nr_sentences' => $nr_sentences, 
			'nr_words' => $nr_words);
		$this->component_model->update_component($component_id, $c);

		$treebank_xml->save($dir . '/total.xml');
	}

	private function upload_to_basex($db, $file)
	{
		try
		{
			// Create session
			$session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

			// Create new database
			$session->create($db, file_get_contents($file));
			echo $session->info();

			// Close session
			$session->close();
		} 
		catch (Exception $e) 
		{
			// print exception
			echo $e->getMessage();
		}
	}
}
