<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Processes all Treebanks. Only available as a command-line script.
	 * @return void
	 */
	public function index()
	{
		if (!is_cli())
		{
			echo 'This script can only be accessed via the command line.' . PHP_EOL;
			return;
		}

		$treebanks = $this->treebank_model->get_to_be_processed_treebanks();
		foreach ($treebanks as $treebank)
		{
			$this->process_treebank($treebank);

			// Send e-mail to User when Treebank is processed
			$user = $this->user_model->get_user_by_id($treebank->user_id);

			$this->email->from(ADMIN_EMAIL, lang('site_title'));
			$this->email->to(ENVIRONMENT === 'development' ? ADMIN_EMAIL : $user->email);
			$this->email->subject(lang('mail_processed_title'));
			$this->email->message(sprintf(lang('mail_processed_body'), $treebank->title, base_url()));

			$this->email->send();
		}
	}

	/**
	 * Processes a single Treebank.
	 * @param  integer $treebank_id The ID of the Treebank.
	 * @return Redirects to the previous page.
	 */
	public function by_id($treebank_id)
	{
		$treebank = $this->treebank_model->get_treebank_by_id($treebank_id);
		$this->process_treebank($treebank);

		$this->session->set_flashdata('message', lang('treebank_processed'));
		redirect($this->agent->referrer(), 'refresh');
	}

	/**
	 * The actual processing of the Treebank.
	 * @param  Treebank $treebank
	 * @return void
	 */
	private function process_treebank($treebank)
	{
		$zip = new ZipArchive;
		$res = $zip->open(UPLOAD_DIR . $treebank->filename);
		if ($res === TRUE)
		{
			$root_dir = UPLOAD_DIR . substr($treebank->filename, 0, -4);
			$zip->extractTo($root_dir);
			$zip->close();

			// Read the metadata
			$metadata = NULL;
			if (file_exists($root_dir . '/metadata.json'))
			{
				$metadata = json_decode(file_get_contents($root_dir . '/metadata.json'));
			}

			// Create databases per component
			foreach(glob($root_dir . '/*', GLOB_ONLYDIR) as $dir)
			{
				// Create a Component for each directory in the .zip-file.
				$slug = basename($dir);
				$basex_db = strtoupper($treebank->title . '_ID_' . $slug);
				$title = $metadata ? $metadata->$slug->description : $slug;

				$component = array(
					'treebank_id' 	=> $treebank->id,
					'title'			=> $title,
					'slug'			=> $slug,
					'basex_db'		=> $basex_db);
				$component_id = $this->component_model->add_component($component);

				// If the Treebank consists of plain text items, tokenize and parse it.
				if ($treebank->is_txt)
				{
					if (!$treebank->is_sent_tokenised) 
					{
						$this->paragraph_tokenize($dir);
					}
					if (!$treebank->is_word_tokenised) 
					{
						$this->word_tokenize($dir);
					}

					$this->alpino_parse($dir, $treebank->has_labels);
				}

				// Merge the (created) XML files, and upload them to BaseX
				$this->merge_xml_files($dir, $component_id);
				$this->basex->upload($basex_db, $dir . '/total.xml');
			}

			// Create the complete treebank, consisting of the individual directories.
			$basex_db = strtoupper($treebank->title . '_ID');
			$treebank_xml = new DOMDocument();
			$treebank_xml->loadXML('<treebank/>');
			foreach (glob($root_dir . '/*', GLOB_ONLYDIR) as $dir)
			{				
				$file_xml = new DOMDocument();
				$file_xml->loadXML(file_get_contents($dir . '/total.xml'));
				foreach ($file_xml->getElementsByTagName('alpino_ds') as $tree)
				{
					$node = $treebank_xml->importNode($tree, TRUE);
					$treebank_xml->documentElement->appendChild($node);
				}
			}
			file_put_contents($root_dir . '/total.xml', $treebank_xml->saveXML($treebank_xml->documentElement));
			$this->basex->upload($basex_db, $root_dir . '/total.xml');

			// Mark treebank as processed
			$this->treebank_model->update_treebank($treebank->id, array('processed' => input_datetime()));
			echo 'Processed!';
		}
		else
		{
			echo 'File not found.';
		}
	}

	private function paragraph_tokenize($dir)
	{
		foreach (glob($dir . '/*.txt') as $file)
		{
			$this->alpino->paragraph_per_line($file);
		}
	}

	private function word_tokenize($dir)
	{
		foreach (glob($dir . '/*.txt') as $file)
		{
			$this->alpino->word_tokenize($file);
		}
	}

	/**
	 * Parses all files in the input to Alpino-DS XML.
	 * @param  string $dir          The directory which contains the .xml-files
	 * @param  boolean $has_labels	Whether the sentence has a label or not.
	 * @return void
	 */
	private function alpino_parse($dir, $has_labels) 
	{
		$id = 0;
		foreach (glob($dir . '/*.txt') as $file)
		{
			$id = $this->alpino->parse($id, $dir, $file, $has_labels);
		}
	}

	/**
	 * Merges all Alpino-DS .xml-files in a directory to a single DomDocument and counts the number of words/sentences.
	 * @param  string $dir           The directory which contains the Alpino-DS .xml-files
	 * @param  integer $component_id The ID of the current Component
	 * @return void
	 */
	private function merge_xml_files($dir, $component_id)
	{
		$nr_sentences = 0;
		$nr_words = 0;

		$treebank_xml = new DOMDocument();
		$treebank_xml->loadXML('<treebank/>');

		foreach (glob($dir . '/*.xml') as $file)
		{
			$file_xml = new DOMDocument();
			$file_xml->loadXML(file_get_contents($file));

			// Set the id attribute as the filename in the root element
			$file_xml->documentElement->setAttribute('id', basename($dir) . '-' . basename($file));

			$xp = new DOMXPath($file_xml);
			$nr_sentences += 1;
			$nr_words += intval($xp->query('//node[@cat="top"]')->item(0)->getAttribute('end'));

			// Attach the document to the original folder
			$node = $treebank_xml->importNode($file_xml->documentElement, TRUE);
			$treebank_xml->documentElement->appendChild($node);

			// Save any existing metadata to the database
			$metadata_nodes = $xp->query('//meta');
			foreach ($metadata_nodes as $metadata_node)
			{
				$field = $metadata_node->getAttribute('name');
				$type = $metadata_node->getAttribute('type');
				$value = $metadata_node->getAttribute('value');

				$metadata = $this->metadata_model->get_metadata_by_field($field);

				if ($metadata)
				{
					$metadata_id = $metadata->id;
				}
				else
				{
					$metadata = array(
						'component_id'	=> $component_id,
						'field'			=> $field,
						'type'			=> $type,
					);
					$metadata_id = $this->metadata_model->add_metadata($metadata);
				}

				$this->metadata_model->update_minmax($metadata_id, $value);
			}
		}

		$c = array(
			'nr_sentences'	=> $nr_sentences, 
			'nr_words' 		=> $nr_words);
		$this->component_model->update_component($component_id, $c);

		file_put_contents($dir . '/total.xml', $treebank_xml->saveXML($treebank_xml->documentElement));
	}
}
