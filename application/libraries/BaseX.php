<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BaseX
{
    public function __construct()
    {
        require_once APPPATH . 'third_party/BaseXClient.php';
    }

	/**
	 * Uploads a .xml-file to a BaseX database
	 * @param  string $db   The BaseX database
	 * @param  string $file The .xml-file
	 * @return void
	 */
	public function upload($db, $file)
	{
		try
		{
			// Create session
			$session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

			// Create new database
			$session->create($db, file_get_contents($file));
			echo $session->info();

			// Close session
			$session->close();
		} 
		catch (Exception $e) 
		{
			// Print exception
			echo $e->getMessage();
		}
	}

	/**
	 * Deletes a database from BaseX.
	 * @param  string $db The database.
	 * @return Nothing.
	 */
	public function delete($db)
	{
		try
		{
			// Create session
			$session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

			// Delete database
			$session->send(sprintf("DROP DB %s", $db));
			echo $session->info();

			// Close session
			$session->close();
		} 
		catch (Exception $e) 
		{
			// Print exception
			echo $e->getMessage();
		}
	}
}
