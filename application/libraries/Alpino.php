<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alpino
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * Parses a plain-text file with possible metadata using the Alpino dependency parser.
	 * @param  integer $id           the ID of the current sentence, used if there are no sentence labels
	 * @param  integer $importrun_id the ID of the current ImportRun
	 * @param  string $dir           the directory of the current file
	 * @param  string $file          the current file
	 * @param  boolean $has_labels   whether or not the current file has sentence labels
	 * @return integer               the last processed sentence identifier in this file
	 */
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
					$parts = array_map('trim', explode('=', $line));
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
					// If this name was not yet present in this block, reset the metadata for this field
					else if (!in_array($name, $metadata_block))
					{
						$metadata[$name] = array();
						array_push($metadata_block, $name);
					}

					// If no value has been provided, remove the name from the metadata array
					if (empty($value))
					{
						unset($metadata[$name]);
					}
					// Otherwise, add the value to the metadata array
					else
					{
						array_push($metadata[$name], $value);
					}
				}
				else
				{
					$id++;

					$this->split_label($id, $line, $has_labels, $label, $in);

					$xml_string = $this->call_alpino($in);

					// Write the result to an XML document, add a proper label and set metadata
					$xml = new DOMDocument();
					$xml->loadXML($xml_string);
					$this->add_label($xml, $label);
					$this->add_metadata($xml, $metadata, $metadata_types);
					$xml->save($dir . '/' . $id . '.xml');

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

	/**
	 * Splits the label of the sentence if the file has labels.
	 * If not, sets the identifier to $label, and the sentence to $in.
	 * @param  integer $id         the ID of the current sentence, used if there are no sentence labels
	 * @param  string $line        the current line
	 * @param  boolean $has_labels whether or not the current file has sentence labels
	 * @param  string &$label      the label for the current line
	 * @param  string &$in         the current line, stripped of the label
	 * @return void                passes $label and $in by reference
	 */
	private function split_label($id, $line, $has_labels, &$label, &$in)
	{
		$label = $id;
		$in = $line;

		if ($has_labels)
		{
			$parts = explode('|', $line);

			if (count($parts) == 1)
			{
				$msg = 'Sentence without label';
				$this->CI->importlog_model->add_log($importrun_id, LogLevel::Warn, $msg, $filename, $linenumber);
			}
			else
			{
				$label = $parts[0];
				$in = substr($line, strlen($label) + 1);
			}
		}
	}

	/**
	 * Calls the Alpino server via a socket on $in, and returns the parsed sentence in (Alpino-)XML.
	 * @param  string $in  the input sentence, tokenized but not yet parsed
	 * @return string      the parsed sentence in XML
	 */
	private function call_alpino($in)
	{
		// Open a socket to Alpino
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		$connect = socket_connect($socket, ALPINO_HOST, ALPINO_PORT);

		// If we can't connect, abort
		if ($connect === FALSE)
		{
			$msg = 'Unable to connect to Alpino server on host ' . ALPINO_HOST . ' and port ' . ALPINO_PORT;
			throw new Exception($msg);
		}

		// Otherwise, write the sentence to the server and obtain the result
		socket_write($socket, $in, strlen($in));
		$result = '';
		while ($out = socket_read($socket, 2048))
		{
			$result .= $out;
		}
		socket_close($socket);

		return $result;
	}

	/**
	 * Attaches the label to the current Alpino-XML DomDocument.
	 * @param DomDocument $xml the current XML DomDocument
	 * @param string $label    the label
	 */
	private function add_label($xml, $label)
	{
		foreach ($xml->getElementsByTagName('sentence') as $sentence)
		{
			$sentence->setAttribute('sentid', $label);
		}
	}

	/**
	 * Attaches the metadata to the current Alpino-XML DomDocument.
	 * @param DomDocument $xml      the current XML DomDocument
	 * @param array $metadata       the metadata present the file
	 * @param array $metadata_types the metadata types present in the file
	 */
	private function add_metadata($xml, $metadata, $metadata_types)
	{
		$mdElement = $xml->createElement('metadata');
		foreach ($metadata as $feature => $values)
		{
			foreach ($values as $value)
			{
				$mElement = $xml->createElement('meta');

				$typeAttribute = $mElement->setAttribute('type', $metadata_types[$feature]);
				$nameAttribute = $mElement->setAttribute('name', $feature);
				$valueAttribute = $mElement->setAttribute('value', $value);

				$mElement->appendChild($typeAttribute);
				$mElement->appendChild($nameAttribute);
				$mElement->appendChild($valueAttribute);

				$mdElement->appendChild($mElement);
			}
		}
		$xml->documentElement->appendChild($mdElement);
	}

	/**
	 * Word-tokenizes a file using Alpino's built-in word tokenizer.
	 * @param  string $file the current file
	 * @return void         replaces the .txt-file with a tokenized file
	 */
	public function word_tokenize($file)
	{
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

	/**
	 * Sentence-tokenizes a file, using Alpino's built-in sentence tokenizer.
	 * @param  string $file the current file
	 * @return void         overwrites the current file with a tokenized file
	 */
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

	/**
	 * Replaces the extension of a file.
	 * @param  string $filename      the current filename
	 * @param  string $new_extension the new extension
	 * @return string                the filename with a new extension
	 */
	private function replace_extension($filename, $new_extension)
	{
    	$info = pathinfo($filename);
    	return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $new_extension;
	}
}
