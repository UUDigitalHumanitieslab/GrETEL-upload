<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Treebank_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    /**
     * Retrieves all Treebanks.
     *
     * @return type
     */
    public function get_all_treebanks()
    {
        return $this->db->get('treebanks')->result();
    }

    /**
     * Rertrieves Treebanks that still need to by processed.
     *
     * @return type
     */
    public function get_to_be_processed_treebanks()
    {
        $this->db->where('processed', null);

        return $this->db->get('treebanks')->result();
    }

    /**
     * Retrieves a Treebank model using its ID.
     *
     * @param int $treebank_id the ID of the Treebank
     *
     * @return Treebank_model the found Treebank
     */
    public function get_treebank_by_id($treebank_id)
    {
        $this->db->where('id', $treebank_id);

        return $this->db->get('treebanks')->row();
    }

    /**
     * Retrieves a Treebank model using its title.
     *
     * @param string $title   the title of the Treebank
     * @param int    $user_id the ID of the User to only get treebanks accessible for this user
     *
     * @return Treebank_model the found Treebank
     */
    public function get_treebank_by_title($title, $user_id = null)
    {
        $this->db->where('title', $title);

        if ($user_id) {
            $this->db->group_start();
            $this->db->where('public', true);
            $this->db->or_where('user_id', $user_id);
            $this->db->group_end();
        }

        return $this->db->get('treebanks')->row();
    }

    /**
     * Creates a new Treebank model.
     *
     * @param array $treebank the fields for the Treebank
     *
     * @return int the new ID for the Treebank
     */
    public function add_treebank($treebank)
    {
        $this->db->insert('treebanks', $treebank);

        return $this->db->insert_id();
    }

    /**
     * Updates a Treebank model.
     *
     * @param int   $treebank_id the ID of the Treebank
     * @param array $treebank    the fields for the Treebank
     */
    public function update_treebank($treebank_id, $treebank)
    {
        $this->db->where('id', $treebank_id);
        $this->db->update('treebanks', $treebank);
    }

    /**
     * Deletes a Treebank model, given its ID.
     *
     * @param int $treebank_id the ID of the Treebank
     */
    public function delete_treebank($treebank_id)
    {
        $this->db->delete('treebanks', array('id' => $treebank_id));
    }

    /////////////////////////
    // API Calls
    /////////////////////////

    /**
     * Returns all Treebanks, including some information on the User that uploaded the Treebank.
     *
     * @return array the found Treebanks
     */
    public function get_api_treebanks()
    {
        $this->db->select(array('treebanks.id', 'treebanks.title',
            'users.id AS user_id', 'users.email',
            'treebanks.uploaded', 'treebanks.processed', 'treebanks.public', ));
        $this->db->from('treebanks');
        $this->db->join('users', 'users.id = treebanks.user_id');

        return $this->db->get()->result();
    }

    /**
     * Returns all processed Treebanks that are either public or uploaded by the given User.
     *
     * @param int $user_id the ID of the User
     *
     * @return array the found Treebanks
     */
    public function get_public_treebanks($user_id = null)
    {
        $this->db->where('processed IS NOT NULL');
        $this->db->group_start();
        $this->db->where('public', true);
        if ($user_id) {
            $this->db->or_where('user_id', $user_id);
        }
        $this->db->group_end();

        return $this->get_api_treebanks();
    }

    /**
     * Returns all Treebanks uploaded by the given User.
     *
     * @param int $user_id the ID of the User
     *
     * @return array the found Treebanks
     */
    public function get_treebanks_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);

        return $this->get_api_treebanks();
    }
}
