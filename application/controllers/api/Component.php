<?php

defined('BASEPATH') or exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

require APPPATH.'/libraries/REST_Controller.php';

class Component extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the metadata of a Component, given its title.
     *
     * @param int    $treebank_id the id of the Treebank
     * @param string $title       the title of the Component
     *
     * @return JSON response
     */
    public function metadata_get($treebank_id, $title)
    {
        $component = $this->component_model->get_component_by_treebank_title($treebank_id, $title);

        if (!$component) {
            $this->response();
        }

        $this->response($this->metadata_model->get_metadata_by_component($component->id, false));
    }
}
