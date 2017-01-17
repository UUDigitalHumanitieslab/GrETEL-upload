<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('treebank_actions'))
{
	/**
	 * Returns the available actions for a Treebank
	 * @param integer $treebank_id The ID of the Treebank.
	 */
	function treebank_actions($treebank_id)
	{
		$CI = & get_instance();
		$treebank = $CI->treebank_model->get_treebank_by_id($treebank_id);

		$actions = array(array('url' => 'treebank/show/' . $treebank->title, 'img' => 'details'));

		if ($treebank->user_id == current_user_id())
		{
			array_push($actions, array('url' => 'treebank/log/' . $treebank->id, 'img' => 'view_log'), array('url' => 'treebank/change_access/' . $treebank->id, 'img' => ($treebank->public ? 'make_private' : 'make_public')), array('url' => 'treebank/delete/' . $treebank->id, 'img' => 'delete')
			);

			if (!$treebank->processed && !$treebank->processing && in_development())
			{
				array_unshift($actions, array('url' => 'cron/process/by_id/' . $treebank->id, 'img' => 'process')
				);
			}
		}

		foreach ($actions as $action)
		{
			$img_src = 'images/' . $action['img'] . '.png';
			echo anchor($action['url'], img(array('src' => $img_src, 'title' => lang($action['img']))));
		}
	}

}
