<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH.'/libraries/CORS_header.php';
require APPPATH.'/libraries/REST_Controller.php';

class User extends REST_Controller
{
    public function __construct()
    {
        $this->allowed_routes = ['index', 'login'];

        parent::__construct();
    }

    /**
     * Downloads a big XML-file containing all the parsed tree.
     */
    public function index_get()
    {
        $user_id = current_user_id();
        if ($user_id) {
            $this->response($this->user_model->get_user_by_id($user_id));
        } else {
            $this->response(['logged_in' => false]);
        }
    }

    /**
     * Validates the given e-mail address and password against the LDAP database.
     * If correct, the session cookie will be created.
     *
     * @return Loads the success view
     */
    public function login_post()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if (!$this->user_status->password_check($username, $password)) {
            $this->response([
                'logged_in' => false,
            ]);
        } else {
            $user = $this->user_status->login($username);
            $this->response($user);
        }
    }

    /**
     * Validates the given e-mail address and password against the LDAP database.
     * If correct, the session cookie will be created.
     *
     * @return Loads the success view
     */
    public function logout_post()
    {
        $this->user_status->logout();
    }
}
