<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('in_development'))
{
	/* Checks whether we are in development mode */
	function in_development()
	{
		return ENVIRONMENT === 'development';
	}
}
