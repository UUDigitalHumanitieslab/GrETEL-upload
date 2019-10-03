<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class BaseX
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        require_once APPPATH.'third_party/BaseXClient.php';
    }

    public function download($db)
    {
        $session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="'.$db.'.xml"');
        $result = $session->executeAndPrint('XQUERY db:open("'.$db.'")/treebank');
        // Close session
        $session->close();
    }

    /**
     * Uploads a .xml-file to a BaseX database.
     *
     * @param int    $importrun_id the ID of the current ImportRun
     * @param string $db           the BaseX database
     * @param string $file         the .xml-file
     */
    public function upload($importrun_id, $db, $file)
    {
        try {
            // Create session
            $session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

            // Create new database
            $session->create($db, file_get_contents($file));
            $this->CI->importlog_model->add_log($importrun_id, LogLevel::Trace, $session->info());

            // Close session
            $session->close();
        } catch (Exception $e) {
            // Log exception
            $this->CI->importlog_model->add_log($importrun_id, LogLevel::Fatal, $e->getMessage());
        }
    }

    /**
     * Deletes a database from BaseX.
     *
     * @param string $db the BaseX database
     */
    public function delete($db)
    {
        try {
            // Create session
            $session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

            // Delete database
            $session->send(sprintf('DROP DB %s', $db));
            echo $session->info();

            // Close session
            $session->close();
        } catch (Exception $e) {
            // Print exception
            echo $e->getMessage();
        }
    }
}
