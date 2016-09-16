<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alpino
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function parse($id, $dir, $file, $has_labels)
	{
		$metadata = array();
		$metadata_types = array();
		$handle = fopen($file, 'r');
		if ($handle) 
		{
			while (($line = fgets($handle)) !== false) 
			{
				if ($line === '') continue; // Don't process empty lines

				// A metadata line in a plain-text file looks like: 
				// ##META text genre = roman
				if (substr($line, 0, 6) === '##META')
				{
					$parts = explode(' ', $line);
					if (!isset($metadata[$parts[2]]))
					{
						$metadata[$parts[2]] = array();
						$metadata_types[$parts[2]] = $parts[1]; // TODO: check against available types
					}
					array_push($metadata[$parts[2]], $parts[4]);
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

					$this->add_metadata($dir, $id, $metadata, $metadata_types);
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

	private function add_metadata($dir, $id, $metadata, $metadata_types)
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
		$cmd = 'cat ' . $file . ' | sh ' . ALPINO_HOME . '/Tokenization/tokenize.sh';
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
}
