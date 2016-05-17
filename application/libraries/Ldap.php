<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ldap
{
    protected $CI;
    protected $server;
    protected $base_dn;
    protected $bind_dn;
    protected $bind_password;
    protected $user_dn;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->config('ldap');

        $this->server = $this->CI->config->item('ldap_server');
        $this->base_dn = $this->CI->config->item('ldap_base_dn');
        $this->bind_dn = $this->CI->config->item('ldap_bind_dn');
        $this->bind_password = $this->CI->config->item('ldap_bind_password');
        $this->user_attribute = $this->CI->config->item('ldap_user_attribute');
    }

    public function update_attributes($dn, $attributes)
    {
        $connection = $this->bind();

        if ($connection)
        {
            ldap_modify($connection, $dn, $attributes);
        }
    }

    public function get_user_attributes($username, $attributes)
    {
        $result = FALSE;
        $connection = $this->bind();

        if ($connection)
        {
            // Search the user
            $search = ldap_search($connection, $this->base_dn, $this->user_filter($username), $attributes);
            $entries = ldap_get_entries($connection, $search);
            if ($entries['count'] != 0)
            {
                $result = $entries[0];
            }
        }

        return $result;
    }

    public function check_credentials($username, $password)
    {
        $result = FALSE;
        $connection = $this->bind();

        if ($connection)
        {
            // Search the user
            $search = ldap_search($connection, $this->base_dn, $this->user_filter($username), array('dn'));
            $entries = ldap_get_entries($connection, $search);
            if ($entries['count'] != 0)
            {
                $binddn = $entries[0]['dn'];
            
                // Try the given password
                $result = @ldap_bind($connection, $binddn, $password);
            }
        }

        return $result;
    }

    private function user_filter($username)
    {
        return '(' . $this->user_attribute . '=' . $username . ')';
    }

    private function bind()
    {
        // Connect to the LDAP server
        $ldapconn = ldap_connect($this->server) or die('Could not connect to LDAP server.');
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        // Bind with the bind account
        $bind = ldap_bind($ldapconn, $this->bind_dn, $this->bind_password);
        if (!$bind) 
        {
            $ldapconn = FALSE;
        }

        return $ldapconn;
    }
}
