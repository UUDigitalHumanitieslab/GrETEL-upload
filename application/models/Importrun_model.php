<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Importrun_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    /**
     * Starts an ImportRun for a Treebank by setting the processing field to TRUE, and cleaning the processed field.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return int the new ID for the ImportRun
     */
    public function start_importrun($treebank_id)
    {
        $this->treebank_model->update_treebank($treebank_id, array('processing' => true, 'processed' => null));

        $this->db->insert('importruns', array('treebank_id' => $treebank_id));

        return $this->db->insert_id();
    }

    /**
     * Ends an ImportRun for a Treebank by setting the processing field to FALSE, and filling the processed field.
     *
     * @param int $importrun_id the ID of the ImportRun
     * @param int $treebank_id  the ID of the Treebank
     */
    public function end_importrun($importrun_id, $treebank_id)
    {
        $processed = input_datetime();
        $this->treebank_model->update_treebank($treebank_id, array('processing' => false, 'processed' => $processed));

        $this->db->where('id', $importrun_id);
        $this->db->update('importruns', array('time_ended' => $processed));
    }

    /**
     * Clears an ImportRun for a Treebank.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return int the new ID for the ImportRun
     */
    public function clear_importrun($treebank_id)
    {
        $this->db->where('treebank_id', $treebank_id);
        $importruns = $this->db->get('importruns')->result();
        $this->treebank_model->update_treebank($treebank_id, array('processing' => false, 'processed' => null));
        $this->component_model->delete_by_treebank($treebank_id);

        foreach ($importruns as $importrun) {
            $this->importlog_model->delete_log($importrun->id);
            $this->db->delete('importruns', array('id' => $importrun->id));
        }
    }

    /**
     * Retrieves the last ImportRun for a Treebank.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return array the found ImportRuns
     */
    public function get_last_importrun_by_treebank($treebank_id)
    {
        $this->db->where('treebank_id', $treebank_id);
        $this->db->order_by('time_ended', 'DESC');

        return $this->db->get('importruns')->row();
    }
}
