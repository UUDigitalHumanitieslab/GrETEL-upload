<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Process extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Processes all Treebanks. Only available as a command-line script.
     */
    public function index()
    {
        if (!is_cli()) {
            echo 'This script can only be accessed via the command line.' . PHP_EOL;

            return;
        }

        ini_set('memory_limit', '2048M');

        $treebanks = $this->treebank_model->get_to_be_processed_treebanks();
        foreach ($treebanks as $treebank) {
            if (!$treebank->processing) {
                $this->process_treebank($treebank);

                // Send e-mail to User when Treebank is processed
                $user = $this->user_model->get_user_by_id($treebank->user_id);

                if ($user->email != GUEST_EMAIL) {
                    $this->email->clear();

                    $this->email->from(ADMIN_EMAIL, lang('site_title'));
                    $this->email->to(in_development() ? ADMIN_EMAIL : $user->email);
                    $this->email->subject(lang('mail_processed_title'));
                    $this->email->message(sprintf(lang('mail_processed_body'), $treebank->title, GRETEL_URL));
                    $this->email->send();
                }
            }
        }
    }

    /**
     * Processes a single Treebank.
     * Only available in development mode.
     *
     * @param int $treebank_id the ID of the Treebank
     */
    public function by_id($treebank_id)
    {
        if (!in_development()) {
            show_404();
        }

        $treebank = $this->treebank_model->get_treebank_by_id($treebank_id);
        $success = $this->process_treebank($treebank);

        if ($success) {
            $this->session->set_flashdata('message', lang('treebank_processed'));
        } else {
            $this->session->set_flashdata('error', lang('treebank_failure'));
        }

        redirect($this->agent->referrer(), 'refresh');
    }

    /**
     * Wrapping the processing of the Treebank.
     *
     * @param Treebank $treebank
     */
    private function process_treebank($treebank)
    {
        $importrun_id = $this->importrun_model->start_importrun($treebank->id);
        $success = true;

        try {
            // make sure errors are always caught and logged
            // otherwise it will just abort for severe errors
            set_error_handler(
                function ($severity, $message, $file, $line) {
                    throw new ErrorException($message, $severity, $severity, $file, $line);
                }
            );
            $success = $this->process_treebank_run($importrun_id, $treebank);
        } catch (Exception $e) {
            $success = false;
            $this->importlog_model->add_log($importrun_id, LogLevel::Error, 'Fatal error processing treebank ' . $treebank->id . ' ' . $e->getMessage());
        } finally {
            restore_error_handler();
            // Mark treebank as processed
            $this->importrun_model->end_importrun($importrun_id, $treebank->id);
        }

        return $success;
    }

    /**
     * The actual processing of the Treebank.
     *
     * @param Treebank $treebank
     */
    private function process_treebank_run($importrun_id, $treebank)
    {
        $zip = new ZipArchive();
        $res = $zip->open(UPLOAD_DIR . $treebank->filename);
        if ($res === true) {
            $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Processing started');

            // create a new random directory, to more easily rerun the task
            $root_dir = UPLOAD_DIR . pathinfo($treebank->filename)['filename'] . '/' . uniqid();
            $zip->extractTo($root_dir . '/in');
            $zip->close();

            // Read the metadata
            $metadata = null;
            if (file_exists($root_dir . '/in/metadata.json')) {
                $metadata = json_decode(file_get_contents($root_dir . '/in/metadata.json'));
            }

            // Create databases per component
            $dirs = $this->retrieve_dirs($root_dir . '/in', $treebank->title);
            $root_len = strlen($root_dir . '/in');
            $basex_db_names = [];
            foreach ($dirs as $dir) {
                // Create a Component for each directory in the .zip-file.
                $relative_dir = substr($dir, $root_len + 1);

                $basex_db = $this->treebank_model->get_db_name($treebank->title, $relative_dir, $slug, $basex_db_names);
                $title = $metadata ? $metadata->$slug->description : $relative_dir;

                $component = [
                    'treebank_id' => $treebank->id,
                    'title' => $title,
                    'slug' => $slug,
                    'basex_db' => $basex_db,
                ];
                $component_id = $this->component_model->add_component($component);

                // If the Treebank consists of plain text items, tokenize and parse it.
                if (in_array($treebank->file_type, [FileType::TXT])) {
                    if (!$treebank->is_sent_tokenised) {
                        $this->paragraph_tokenize($dir);
                    }
                    if (!$treebank->is_word_tokenised) {
                        $this->word_tokenize($dir);
                    }

                    // currently these text files are converted in-place to Lassy XML files
                    $this->alpino_parse($importrun_id, $dir, $treebank->has_labels);

                    foreach (glob($dir . '/*.txt') as $file) {
                        // remove files to prevent them from being parsed again by corpus2alpino
                        unlink($file);
                    }
                }

                $this->corpus_parse($importrun_id, $root_dir, $relative_dir);

                // Merge the (created) XML files, and upload them to BaseX
                $this->merge_xml_files($root_dir, $relative_dir, $importrun_id, $treebank->id, $component_id);
                $merged_xml_path = $root_dir . '/out/' . $relative_dir . '/__total__.xml';
                if (file_exists($merged_xml_path)) {
                    $this->basex->upload($importrun_id, $basex_db, $merged_xml_path);
                }
            }

            // Merge all the directories, and upload the merged file to BaseX
            $this->merge_dirs($root_dir, $dirs, $importrun_id);
            $basex_db = $this->treebank_model->get_db_name($treebank->title);
            $this->basex->upload($importrun_id, $basex_db, $root_dir . '/out/__total__.xml');

            $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Processing completed');

            return true;
        } else {
            $this->importlog_model->add_log($importrun_id, LogLevel::Fatal, 'File not found: ' . UPLOAD_DIR . $treebank->filename);

            return false;
        }
    }

    /**
     * Scans the root directory for sub-directories.
     * If there are none, a sub-directory is created and files in the root directory are moved there.
     *
     * @param string $root_dir       The root directory
     * @param string $treebank_title The title of the Treebank
     *
     * @return array the array of subdirectories in this directory
     */
    private function retrieve_dirs($root_dir, $treebank_title = null)
    {
        // Retrieve the directories in this .zip-file
        $dirs = glob($root_dir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $subdirs = $this->retrieve_dirs($dir);
            foreach ($subdirs as $subdir) {
                $dirs[] = $subdir;
            }
        }

        // If no directories are found, create a new folder and move files in the root directory there
        if (!$dirs && $treebank_title != null) {
            $filenames = scandir($root_dir);
            $new_dir = $root_dir . '/' . $treebank_title;
            mkdir($new_dir, 0755, true);
            foreach ($filenames as $filename) {
                if ($filename != '.' && $filename != '..') {
                    rename($root_dir . '/' . $filename, $new_dir . '/' . $filename);
                }
            }
            $dirs = [$new_dir];
        }

        return $dirs;
    }

    /**
     * Paragraph-tokenizes each .txt-file in the specified directory.
     *
     * @param string $dir the directory
     */
    private function paragraph_tokenize($dir)
    {
        foreach (glob($dir . '/*.txt') as $file) {
            $this->alpino->paragraph_per_line($file);
        }
    }

    /**
     * Sentence-tokenizes each .txt-file in the specified directory.
     *
     * @param string $dir the directory
     */
    private function word_tokenize($dir)
    {
        foreach (glob($dir . '/*.txt') as $file) {
            $this->alpino->word_tokenize($file);
        }
    }

    /**
     * Completely parse files using the corpus2alpino program.
     *
     * @param int    $importrun_id The ID of the current ImportRun
     * @param string $root_dir     The root directory
     * @param string $relative_dir The path of the directory which contains the files relative to the input folder
     */
    private function corpus_parse($importrun_id, $root_dir, $relative_dir)
    {
        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Started corpus2alpino preprocessing');
        foreach (glob($root_dir . '/in/' . $relative_dir . '/*.{xml,cha,txt}', GLOB_BRACE) as $file) {
            if (!$this->corpus2alpino($root_dir, $relative_dir, $file, $importrun_id)) {
                $this->importlog_model->add_log($importrun_id, LogLevel::Error, 'Aborted corpus2alpino preprocessing');

                return;
            }
        }

        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Completed corpus2alpino preprocessing');
    }

    /**
     * Converts a FoLiA/TEI file to readable input.
     *
     * @param string $root_dir     the directory containing the input and output files
     * @param string $relative_dir the path of the directory being parsed relative to the input folder
     * @param string $file_path    the full path of the FoLiA file to parse
     * @param int    $importrun_id The ID of the current ImportRun
     */
    private function corpus2alpino($root_dir, $relative_dir, $file_path, $importrun_id)
    {
        $this->importlog_model->add_log($importrun_id, LogLevel::Debug, 'Corpus2alpino on ' . $file_path);
        $command = 'export LANG=nl_NL.UTF8 && ' . $this->config->item('corpus2alpino_path') . ' -t -s ' . ALPINO_HOST . ':' . ALPINO_PORT .
            ' ' . escapeshellarg($file_path) . ' -o ' . escapeshellarg("{$root_dir}/out/{$relative_dir}") . ' 2>&1';
        $output = [];

        // also have a log which isn't truncated (for extensive debugging)
        $log = fopen($file_path . '.log', 'w');
        try {
            exec($command, $output, $return_var);
            foreach ($output as $line) {
                fwrite($log, $line);
                $this->importlog_model->add_log($importrun_id, LogLevel::Debug, $line);
            }
        } finally {
            fclose($log);
        }
        if ($return_var != 0) {
            $this->importlog_model->add_log($importrun_id, LogLevel::Error, "Problem executing corpus2alpino. Is it installed? Check the Apache log or inspect {$file_path}.");

            return false;
        }

        return true;
    }

    /**
     * Parses all files in the input to Alpino-DS XML.
     *
     * @param int    $importrun_id The ID of the current ImportRun
     * @param string $dir          The directory which contains the .xml-files
     * @param bool   $has_labels   whether the sentence has a label or not
     */
    private function alpino_parse($importrun_id, $dir, $has_labels)
    {
        $id = 0;
        foreach (glob($dir . '/*.txt') as $file) {
            try {
                $id = $this->alpino->parse($id, $importrun_id, $dir, $file, $has_labels);
            } catch (Exception $e) {
                $this->importlog_model->add_log($importrun_id, LogLevel::Fatal, $e->getMessage());

                return;
            }
        }
    }

    private function sentence_id($root_dir, $file_path)
    {
        // strip the root directory (and output folder) from the file path
        // this should give a unique path to the file
        $relative_path = str_replace($root_dir . '/out/', '', $file_path);

        // files containing multiple sentences are split into:
        // filename/1...n.xml
        // replace this with filename:1...n
        $sentid = preg_replace('/(?<=\.(xml|cha|txt))[\/\\\\](\d+)\.xml$/i', ':$2', $relative_path);

        // but now it isn't guaranteed unique! Ohnoes!
        if (!isset($this->unique_sentid)) {
            $this->unique_sentid = [$sentid];
        } else {
            $base_sentid = $sentid;
            $i = 1;
            while (in_array($sentid, $this->unique_sentid)) {
                $sentid = $base_sentid . '-' . $i;
                ++$i;
            }
            $this->unique_sentid[] = $sentid;
        }

        return $sentid;
    }

    /**
     * Merges all Alpino-DS .xml-files in a directory to a single DomDocument and counts the number of words/sentences.
     *
     * @param string $root_dir     The root directory
     * @param string $relative_dir The path of the directory which contains the files relative to the input folder
     * @param int    $importrun_id The ID of the current ImportRun
     * @param int    $treebank_id  The ID of the current Treebank
     * @param int    $component_id The ID of the current Component
     */
    private function merge_xml_files($root_dir, $relative_dir, $importrun_id, $treebank_id, $component_id)
    {
        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Starting merge of directory ' . $relative_dir);

        $nr_sentences = 0;
        $nr_words = 0;

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('treebank');

        $file_index = 0;

        $files = glob($root_dir . '/out/' . $relative_dir . '/*.{xml,cha,txt}', GLOB_BRACE);
        natsort($files);
        while ($file = array_shift($files)) {
            try {
                $file_content = file_get_contents($file);
                $header_length = min(strlen($file_content), 100);
                if (
                    substr_count($file_content, 'folia2html.xsl', 0, $header_length) > 0 ||
                    substr_count($file_content, '<TEI', 0, $header_length) > 0
                ) {
                    // skip FoLiA and TEI files: these should already have been pre-processed
                    $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Skip FoLiA/TEI ' . $file);
                    continue;
                }

                // CHAT time alignment character, replaced with middle dot because DOMDocument does not like this entity at all
                $file_content = str_replace('', '&#183;', $file_content);
                if (!$file_content) {
                    $sub_files = glob($file . '/*.xml');
                    $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Looking for sub-files ' . $file . '/*.xml');

                    if ($sub_files) {
                        natsort($sub_files);
                        $files = array_merge($sub_files, $files);
                    } else {
                        $this->importlog_model->add_log($importrun_id, LogLevel::Warn, 'Empty file ' . $file);
                    }

                    continue;
                }
                $file_extension = pathinfo($file)['extension'];
                switch (strtolower($file_extension)) {
                    case 'xml':
                        break;
                    default:
                        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Skipped ' . $file . ' without xml-extension');
                        continue 2;
                        break;
                }

                $file_xml = new DOMDocument();
                $file_xml->loadXML($file_content);
                if (!$file_xml) {
                    $this->importlog_model->add_log($importrun_id, LogLevel::Warn, 'Could not load the XML of ' . $file);
                    continue;
                }

                // Set the id attribute as the relative path and filename in the root element
                // this way each sentence id should be unique
                $sentence_id = $this->sentence_id($root_dir, $file);
                $file_xml->documentElement->setAttribute('id', $sentence_id);

                $xp = new DOMXPath($file_xml);
                ++$nr_sentences;
                $node = $xp->query('//node[@cat="top"]')->item(0);
                if ($node != null) {
                    // empty
                    $nr_words += intval($node->getAttribute('end'));
                }

                // Attach the document to the original folder
                $str = $file_xml->saveXML($file_xml->documentElement);
                $xmlWriter->writeRaw($str);

                // Save any existing metadata to the database
                $metadata_nodes = $xp->query('//meta');
                foreach ($metadata_nodes as $metadata_node) {
                    $field = $metadata_node->getAttribute('name');
                    $type = $metadata_node->getAttribute('type');
                    $value = $metadata_node->getAttribute('value');

                    $metadata = $this->metadata_model->get_metadata_by_treebank_field($treebank_id, $field);

                    if ($metadata) {
                        $metadata_id = $metadata->id;
                    } else {
                        $metadata = [
                            'treebank_id' => $treebank_id,
                            'field' => $field,
                            'type' => $type,
                            'facet' => default_facet($type),
                        ];
                        $metadata_id = $this->metadata_model->add_metadata($metadata);
                    }

                    $this->metadata_model->update_minmax($metadata_id, $value);
                }

                // Flush XML in memory to file every 100 sentences
                if ($nr_sentences % 100 == 0) {
                    file_put_contents($root_dir . '/out/' . $relative_dir . '/__total__.xml', $xmlWriter->flush(), FILE_APPEND);
                }
            } catch (Exception $e) {
                $this->importlog_model->add_log($importrun_id, LogLevel::Error, 'Problem loading ' . $file . ' ' . $e->getMessage());
            }
        }

        try {
            $xmlWriter->endElement();
            $xmlWriter->endDocument();
        } catch (Exception $e) {
            $this->importlog_model->add_log($importrun_id, LogLevel::Error, 'Problem closing total XML ' . $e->getMessage());
        }

        if ($nr_sentences) {
            // don't write a file if there was no output
            $c = [
                'nr_sentences' => $nr_sentences,
                'nr_words' => $nr_words,
            ];
            $this->component_model->update_component($component_id, $c);

            file_put_contents($root_dir . '/out/' . $relative_dir . '/__total__.xml', $xmlWriter->flush(), FILE_APPEND);
        } else {
            // Skip empty components
            $this->component_model->delete_component($component_id);
            $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Deleted component ' . $relative_dir . ' because it was empty ');
        }

        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Finished merge of directory ' . $relative_dir . ' #sentences=' . $nr_sentences);
    }

    /**
     * Create the complete Treebank, consisting of the individual directories.
     *
     * @param string $root_dir     The root directory
     * @param array  $dirs         The Component directories
     * @param int    $importrun_id The ID of the current ImportRun
     */
    private function merge_dirs($root_dir, $dirs, $importrun_id)
    {
        $this->importlog_model->add_log($importrun_id, LogLevel::Trace, 'Started total merge');

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('treebank');

        $i = 0;
        foreach ($dirs as $in_dir) {
            $out_dir = str_replace($root_dir . '/in', $root_dir . '/out', $in_dir);
            $total_path = $out_dir . '/__total__.xml';
            if (!file_exists($total_path)) {
                continue;
            }
            try {
                $xmlReader = new XMLReader();
                $xmlReader->open($total_path);

                // Select all alpino_ds elements, write to the total file
                while ($xmlReader->read() && $xmlReader->name !== 'alpino_ds');
                while ($xmlReader->name === 'alpino_ds') {
                    $xmlWriter->writeRaw($xmlReader->readOuterXML());
                    $xmlReader->next('alpino_ds');
                }

                $xmlReader->close();

                // Flush XML in memory to file every 1000 iterations
                if ($i % 1000 == 0) {
                    file_put_contents($root_dir . '/out/__total__.xml', $xmlWriter->flush(true), FILE_APPEND);
                }
            } catch (Exception $e) {
                $this->importlog_model->add_log($importrun_id, LogLevel::Error, 'Problem merging ' . $total_path . ' ' . $e->getMessage());
            }

            ++$i;
        }

        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        file_put_contents($root_dir . '/out/__total__.xml', $xmlWriter->flush(true), FILE_APPEND);

        $this->importlog_model->add_log($importrun_id, LogLevel::Info, 'Finished total merge');
    }
}
