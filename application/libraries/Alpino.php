<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alpino
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function parse($id, $importrun_id, $dir, $file, $has_labels)
	{
		// Instantiate variables for Metadata processing
		$metadata_block = array();
		$metadata = array();
		$metadata_types = array();
		
		// We set the filename to be foldername + basename
		$filename = basename(dirname($file)) . DIRECTORY_SEPARATOR . basename($file);
		$linenumber = 0;

		$handle = fopen($file, 'r');
		if ($handle)
		{
			while (($line = fgets($handle)) !== FALSE)
			{
				$linenumber++;

				// Don't process empty lines, but empty the metadata block
				if (trim($line) == '')
				{
					$metadata_block = array();
					continue;
				}

				// A metadata line in a plain-text file looks like: 
				// ##META text  genre = newspaper
				// which we should map to:
				//        $type $name   $value
				if (substr($line, 0, 6) === '##META')
				{
					$parts = array_map('trim', explode(' = ', $line));
					if (count($parts) !== 2)
					{
						$msg = 'Metadata line not properly separated';
						$this->CI->importlog_model->add_log($importrun_id, LogLevel::Warn, $msg, $filename, $linenumber);
						continue;
					}

					$specs = array_map('trim', explode(' ', $parts[0]));
					$value = $parts[1];

					if (count($specs) !== 3)
					{
						$msg = 'No proper metadata specification';
						$this->CI->importlog_model->add_log($importrun_id, LogLevel::Warn, $msg, $filename, $linenumber);
						continue;
					}

					$type = $specs[1];
					$name = $specs[2];

					if (!MetadataType::isValidValue($type))
					{
						$msg = 'Unknown metadata type "' . $type . '"';
						$this->CI->importlog_model->add_log($importrun_id, LogLevel::Warn, $msg, $filename, $linenumber);
						continue;
					}

					// If we haven't had any metadata with this name yet, add an empty array of values
					if (!isset($metadata[$name]))
					{
						$metadata[$name] = array();
						$metadata_types[$name] = $type;
					}
					// If we came out this data had not yet defined in this block, reset the metadata for this field
					else if (!in_array($name, $metadata_block))
					{
						$metadata[$name] = array();
						array_push($metadata_block, $name);
					}
					// Add the new value to the metadata array
					array_push($metadata[$name], $value);
				}
				else
				{
					$id++;

					// Call Alpino to parse the current sentence
					$cmd = ALPINO_HOME . '/bin/Alpino -notk -veryfast user_max=180000 -end_hook=xml -parse -flag treebank ' . $dir;
					$descriptorspec = array(
						0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
						1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
						//2 => array('file', TMP_DIR . '/alpino.log', 'a') // stderr is a file to write to
					);
					$cwd = NULL;
					$env = array('ALPINO_HOME' => ALPINO_HOME);
					$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

					if (is_resource($process))
					{
						$in = $has_labels ? $line : ($id . '|' . $line);
						fwrite($pipes[0], $in);
						fclose($pipes[0]);

						echo stream_get_contents($pipes[1]);
						fclose($pipes[1]);

						proc_close($process);
					}

					$this->add_metadata($id, $dir, $metadata, $metadata_types);

					// We are now in a text block, empty the metadata block
					$metadata_block = array();
				}
			}

			fclose($handle);

			return $id;
		}
		else
		{
			echo 'Error opening file.';
		}
	}

	private function add_metadata($id, $dir, $metadata, $metadata_types)
	{
		$xml_file = $dir . '/' . $id . '.xml';
		$file_xml = new DOMDocument();
		$file_xml->loadXML(file_get_contents($xml_file));
		
		$mdElement = $file_xml->createElement('metadata');
		foreach ($metadata as $feature => $values)
		{
			foreach ($values as $value)
			{
				$mElement = $file_xml->createElement('meta');

				$typeAttribute = $mElement->setAttribute('type', $metadata_types[$feature]);
				$nameAttribute = $mElement->setAttribute('name', $feature);
				$valueAttribute = $mElement->setAttribute('value', $value);

				$mElement->appendChild($typeAttribute);
				$mElement->appendChild($nameAttribute);
				$mElement->appendChild($valueAttribute);

				$mdElement->appendChild($mElement);
			}
		}
		$file_xml->documentElement->appendChild($mdElement);
		$file_xml->save($xml_file);
	}

	public function word_tokenize($file)
	{
		// Rename the in-file
		$in = $this->replace_extension($file, 'in');
		$out = $file;
		rename($file, $in);

		$handle = fopen($in, 'r');
		if ($handle)
		{
			while (($line = fgets($handle)) !== FALSE)
			{
				// Skip empty/metadata lines
				if (trim($line) == '' || substr($line, 0, 6) === '##META')
				{
					file_put_contents($out, $line, FILE_APPEND);
					continue;
				}

				$cmd = 'echo "' . $line . '" | sh ' . ALPINO_HOME . '/Tokenization/tokenize.sh';
				$descriptorspec = array(
					0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
					1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
				);
				$cwd = NULL;
				$env = array('ALPINO_HOME' => ALPINO_HOME);
				$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

				if (is_resource($process))
				{
					file_put_contents($out, stream_get_contents($pipes[1]), FILE_APPEND);
					fclose($pipes[1]);

					proc_close($process);
				}
			}

			fclose($handle);
		}
		else
		{
			echo 'Error opening file.';
		}
	}

	public function paragraph_per_line($file)
	{
		$cmd = '/usr/bin/perl -w ' . ALPINO_HOME . '/Tokenization/paragraph_per_line ' . $file;
		$descriptorspec = array(
			0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
			1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
		);
		$cwd = NULL;
		$env = array('ALPINO_HOME' => ALPINO_HOME);
		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

		if (is_resource($process))
		{
			// TODO: write this to separate file?
			file_put_contents($file, stream_get_contents($pipes[1]));
			fclose($pipes[1]);

			proc_close($process);
		}
	}

	private function replace_extension($filename, $new_extension)
	{
    	$info = pathinfo($filename);
    	return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $new_extension;
	}
}
