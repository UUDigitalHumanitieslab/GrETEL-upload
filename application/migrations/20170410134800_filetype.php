<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_FileType extends CI_Migration
{

	public function up()
	{
		$fields = array(
			'file_type' => array('type' => 'ENUM("CHAT", "txt", "LASSY", "FoLiA", "TEI")', 'default' => 'txt', 'null' => FALSE),
		);

		$this->dbforge->add_column('treebanks', $fields);

		foreach ($this->treebank_model->get_all_treebanks() as $treebank)
		{
			$file_type = $treebank->is_txt ? 'txt' : 'LASSY';
			$t = array('file_type' => $file_type);
			$this->treebank_model->update_treebank($treebank->id, $t);
		}

		$this->dbforge->drop_column('treebanks', 'is_txt');
	}

	public function down()
	{
		$fields = array(
			'is_txt' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
		);

		$this->dbforge->add_column('treebanks', $fields);

		foreach ($this->treebank_model->get_all_treebanks() as $treebank)
		{
			$is_txt = $treebank->file_type == 'txt' ? TRUE : FALSE;
			$t = array('is_txt' => $is_txt);
			$this->treebank_model->update_treebank($treebank->id, $t);
		}

		$this->dbforge->drop_column('treebanks', 'file_type');
	}

}
