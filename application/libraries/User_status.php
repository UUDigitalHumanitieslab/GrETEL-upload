<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_status
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function login($username)
    {
        $user = $this->CI->user_model->get_user_by_username($username);

        // If we can't find the User, create a new one
        if (!$user) {
            $email = $this->CI->ldap->get_user_attributes($username, ['mail'])['mail'][0];

            $user = [
                'username' => $username,
                'email' => $email,
            ];
            $user_id = $this->CI->user_model->create_user($user);
        }
        // Else, retrieve the ID from the found User
        else {
            $user_id = $user->id;
        }

        // Set the userdata
        $this->CI->session->set_userdata([
            'logged_in' => true,
            'user_id' => $user_id,
        ]);

        return [
            'logged_in' => true,
            'id' => $user_id,
            'username' => $username,
        ];
    }

    public function logout()
    {
        $this->CI->session->sess_destroy();
    }

    /**
     * Checks the password against the LDAP database.
     *
     * @param string $username the supplied username
     *
     * @return bool whether or not the authentication has succeeded
     */
    public function password_check($username, $password)
    {
        return $this->CI->ldap->check_credentials($username, $password);
    }
}
