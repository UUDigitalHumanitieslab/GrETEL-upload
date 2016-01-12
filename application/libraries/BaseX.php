<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BaseX
{
    public function __construct()
    {
        require_once APPPATH . 'third_party/BaseXClient.php';
    }
}
