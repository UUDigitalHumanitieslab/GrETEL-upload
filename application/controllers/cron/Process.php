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
			if (!$treebank->processing)
			{
				$this->process_treebank($treebank);

				// Send e-mail to User when Treebank is processed
				$user = $this->user_model->get_user_by_id($treebank->user_id);

				$this->email->clear();

				$this->email->from(ADMIN_EMAIL, lang('site_title'));
				$this->email->to(in_development() ? ADMIN_EMAIL : $user->email);
				$this->email->subject(lang('mail_processed_title'));
				$this->email->message(sprintf(lang('mail_processed_body'), $treebank->title, GRETEL_URL));
				$this->email->send();
			}
		}
	}

	/**
	 * Processes a single Treebank.
	 * Only available in development mode.
	 * @param  integer $treebank_id The ID of the Treebank.
	 * @return void                 Redirects to the previous page.
	 */
	public function by_id($treebank_id)
	{
		if (!in_development())
		{
			show_404();
		}

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
		$importrun_id = $this->importrun_model->start_importrun($treebank->id);

		$zip = new ZipArchive;
		$res = $zip->open(UPLOAD_DIR . $treebank->filename);
		if ($res === TRUE)
		{
			$this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Processing started');

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
			$dirs = $this->retrieve_dirs($root_dir, $treebank->title);
			foreach ($dirs as $dir)
			{
				// Create a Component for each directory in the .zip-file.
				$slug = basename($dir);
				$basex_db = strtoupper($treebank->title . '_ID_' . $slug);
				$title = $metadata ? $metadata->$slug->description : $slug;

				$component = array(
					'treebank_id' => $treebank->id,
					'title' => $title,
					'slug' => $slug,
					'basex_db' => $basex_db);
				$component_id = $this->component_model->add_component($component);

				// If the Treebank consists of CHAT files, tokenize and parse it.
				if (in_array($treebank->file_type, array(FileType::CHAT)))
				{
					$this->chat_preprocess($importrun_id, $root_dir, $dir);
					$this->alpino_parse($importrun_id, $dir, FALSE);
				}

				// If the Treebank consists of plain text items, tokenize and parse it.
				if (in_array($treebank->file_type, array(FileType::TXT)))
				{
					if (!$treebank->is_sent_tokenised)
					{
						$this->paragraph_tokenize($dir);
					}
					if (!$treebank->is_word_tokenised)
					{
						$this->word_tokenize($dir);
					}

					$this->alpino_parse($importrun_id, $dir, $treebank->has_labels);
				}

				// Merge the (created) XML files, and upload them to BaseX
				$this->merge_xml_files($dir, $importrun_id, $treebank->id, $component_id);
				$this->basex->upload($importrun_id, $basex_db, $dir . '/total.xml');
			}

			// Merge all the directories, and upload the merged file to BaseX
			$this->merge_dirs($root_dir, $dirs, $importrun_id);
			$basex_db = strtoupper($treebank->title . '_ID');
			$this->basex->upload($importrun_id, $basex_db, $root_dir . '/total.xml');

			$this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Processing completed');
		}
		else
		{
			$this->importlog_model->add_log($importrun_id, LogLevel::Fatal, 'File not found');
		}

		// Mark treebank as processed
		$this->importrun_model->end_importrun($importrun_id, $treebank->id);
	}

	/**
	 * Scans the root directory for sub-directories. 
	 * If there are none, a sub-directory is created and files in the root directory are moved there.
	 * @param string $root_dir       The root directory
	 * @param string $treebank_title The title of the Treebank
	 * @return array                 The array of subdirectories in this directory.
	 */
	private function retrieve_dirs($root_dir, $treebank_title)
	{
		// Retrieve the directories in this .zip-file
		$dirs = glob($root_dir . '/*', GLOB_ONLYDIR);

		// If no directories are found, create a new folder and move files in the root directory there
		if (!$dirs)
		{
			$filenames = scandir($root_dir);
			$new_dir = $root_dir . '/' . $treebank_title;
			mkdir($new_dir, 0755, TRUE);
			foreach ($filenames as $filename)
			{
				if ($filename != '.' && $filename != '..')
				{
					rename($root_dir . '/' . $filename, $new_dir . '/' . $filename);
				}
			}
			$dirs = array($new_dir);
		}

		return $dirs;
	}
	
	/**
	 * Paragraph-tokenizes each .txt-file in the specified directory.
	 * @param string $dir The directory.
	 */
	private function paragraph_tokenize($dir)
	{
		foreach (glob($dir . '/*.txt') as $file)
		{
			$this->alpino->paragraph_per_line($file);
		}
	}

	/**
	 * Sentence-tokenizes each .txt-file in the specified directory.
	 * @param string $dir The directory.
	 */
	private function word_tokenize($dir)
	{
		foreach (glob($dir . '/*.txt') as $file)
		{
			$this->alpino->word_tokenize($file);
		}
	}

	/**
	 * Preprocesses CHAT files using the program CHAMD by Jan Odijk.
	 * @param integer $importrun_id The ID of the current ImportRun
	 * @param string $root_dir      The root directory
	 * @param string $dir           The directory which contains the .cha-files
	 */
	private function chat_preprocess($importrun_id, $root_dir, $dir)
	{
		$this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Started CHAT preprocessing');

		$logfile = $dir . '/chamd.log';
		$command = 'python3 ' . CHAMD_HOME . '/chamd.py';
		$command .= ' --path=' . $dir;
		$command .= ' --outpath=' . $root_dir;
		$command .= ' --logfile=' . $logfile;
		shell_exec($command);

		$handle = fopen($logfile, 'r');
		if ($handle)
		{
			while (($line = fgets($handle)) !== FALSE)
			{
				$this->importlog_model->add_log($importrun_id, LogLevel::Warn, $line);
			}

			fclose($handle);
		}
		
		$this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Completed CHAT preprocessing');
	}

	/**
	 * Parses all files in the input to Alpino-DS XML.
	 * @param  integer $importrun_id The ID of the current ImportRun
	 * @param  string $dir          The directory which contains the .xml-files
	 * @param  boolean $has_labels	Whether the sentence has a label or not.
	 * @return void
	 */
	private function alpino_parse($importrun_id, $dir, $has_labels)
	{
		$id = 0;
		foreach (glob($dir . '/*.txt') as $file)
		{
			try
			{
				$id = $this->alpino->parse($id, $importrun_id, $dir, $file, $has_labels);
			}
			catch (Exception $e)
			{
				$this->importlog_model->add_log($importrun_id, LogLevel::Fatal, $e->getMessage());
				return;
			}
		}
	}

	/**
	 * Merges all Alpino-DS .xml-files in a directory to a single DomDocument and counts the number of words/sentences.
	 * @param  string $dir           The directory which contains the Alpino-DS .xml-files
	 * @param  integer $importrun_id The ID of the current ImportRun
	 * @param  integer $treebank_id  The ID of the current Treebank
	 * @param  integer $component_id The ID of the current Component
	 */
	private function merge_xml_files($dir, $importrun_id, $treebank_id, $component_id)
	{
		$this->importlog_model->add_log($importrun_id, LogLevel::Trace, 'Starting merge of directory ' . $dir);

		$nr_sentences = 0;
		$nr_words = 0;

		$xmlWriter = new XMLWriter();
		$xmlWriter->openMemory();
		$xmlWriter->startDocument('1.0', 'UTF-8');
		$xmlWriter->startElement('treebank');

		$i = 0;
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
			$str = $file_xml->saveXML($file_xml->documentElement);
			$xmlWriter->writeRaw($str);

			// Save any existing metadata to the database
			$metadata_nodes = $xp->query('//meta');
			foreach ($metadata_nodes as $metadata_node)
			{
				$field = $metadata_node->getAttribute('name');
				$type = $metadata_node->getAttribute('type');
				$value = $metadata_node->getAttribute('value');

				$metadata = $this->metadata_model->get_metadata_by_treebank_field($treebank_id, $field);

				if ($metadata)
				{
					$metadata_id = $metadata->id;
				}
				else
				{
					$metadata = array(
						'treebank_id' => $treebank_id,
						'field' => $field,
						'type' => $type,
						'facet' => default_facet($type),
					);
					$metadata_id = $this->metadata_model->add_metadata($metadata);
				}

				$this->metadata_model->update_minmax($metadata_id, $value);
			}

			// Flush XML in memory to file every 1000 iterations
			if ($i % 1000 == 0)
			{
				file_put_contents($dir . '/total.xml', $xmlWriter->flush(), FILE_APPEND);
			}

			$i++;
		}

		$c = array(
			'nr_sentences' => $nr_sentences,
			'nr_words' => $nr_words);
		$this->component_model->update_component($component_id, $c);

		$xmlWriter->endElement();
		$xmlWriter->endDocument();
		file_put_contents($dir . '/total.xml', $xmlWriter->flush(), FILE_APPEND);

		$this->importlog_model->add_log($importrun_id, LogLevel::Trace, 'Finished merge of directory ' . $dir);
	}

	/**
	 * Create the complete Treebank, consisting of the individual directories.
	 * @param string $root_dir      The root directory
	 * @param array $dirs           The Component directories
	 * @param integer $importrun_id The ID of the current ImportRun
	 */
	private function merge_dirs($root_dir, $dirs, $importrun_id)
	{
		$this->importlog_model->add_log($importrun_id, LogLevel::Trace, 'Started total merge');

		$xmlWriter = new XMLWriter();
		$xmlWriter->openMemory();
		$xmlWriter->startDocument('1.0', 'UTF-8');
		$xmlWriter->startElement('treebank');

		$i = 0;
		foreach ($dirs as $dir)
		{
			$xmlReader = new XMLReader();
			$xmlReader->open($dir . '/total.xml');

			// Select all alpino_ds elements, write to the total file
			while ($xmlReader->read() && $xmlReader->name !== 'alpino_ds');
			while ($xmlReader->name === 'alpino_ds')
			{
				$xmlWriter->writeRaw($xmlReader->readOuterXML());
				$xmlReader->next('alpino_ds');
			}

			$xmlReader->close();

			// Flush XML in memory to file every 1000 iterations
			if ($i % 1000 == 0)
			{
				file_put_contents($root_dir . '/total.xml', $xmlWriter->flush(true), FILE_APPEND);
			}

			$i++;
		}

		$xmlWriter->endElement();
		$xmlWriter->endDocument();
		file_put_contents($root_dir . '/total.xml', $xmlWriter->flush(true), FILE_APPEND);

		$this->importlog_model->add_log($importrun_id, LogLevel::Trace, 'Finished total merge');
	}

}
