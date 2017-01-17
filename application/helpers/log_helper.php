<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/** Log levels */
class LogLevel extends BasicEnum
{

	const Trace = 'trace';
	const Debug = 'debug';
	const Info = 'info';
	const Warn = 'warn';
	const Error = 'error';
	const Fatal = 'fatal';

}
