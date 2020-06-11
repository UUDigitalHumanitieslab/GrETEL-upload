<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** File types */
class FileType extends BasicEnum
{
    const CHAT = 'CHAT';
    const TXT = 'txt';
    const LASSY = 'LASSY';
    const FOLIA = 'FoLiA';
    const TEI = 'TEI';
}

if (!function_exists('treebank_actions')) {
    /**
     * Returns the available actions for a Treebank.
     *
     * @param int $treebank_id the ID of the Treebank
     */
    function treebank_actions($treebank_id)
    {
        $CI = &get_instance();
        $treebank = $CI->treebank_model->get_treebank_by_id($treebank_id);

        $details = array('url' => 'treebank/show/'.$treebank->title, 'img' => 'details');

        if ($treebank->user_id == current_user_id()) {
            $processable = !$treebank->processed && !$treebank->processing;
            $actions = array_filter(
                [
                    array_merge($details, array('include' => !$processable)),
                    array('url' => 'cron/process/by_id/'.$treebank->id, 'img' => 'process', 'include' => $processable && in_development()),
                    array('url' => 'treebank/log/'.$treebank->id, 'img' => 'view_log'),
                    array('url' => 'treebank/change_access/'.$treebank->id, 'img' => ($treebank->public ? 'make_private' : 'make_public')),
                    array('url' => 'api/treebank/download/'.$treebank->title, 'img' => 'drive_web'),
                    array('url' => 'treebank/reset/'.$treebank->id, 'img' => 'reset', 'include' => !$processable),
                    array('url' => 'treebank/delete/'.$treebank->id, 'img' => 'delete'),
                ],
                function ($item) {
                    return !array_key_exists('include', $item) || $item['include'];
                });
        } else {
            $actions = array($details);
        }

        foreach ($actions as $action) {
            $img_src = 'images/'.$action['img'].'.png';
            echo anchor($action['url'], img(array('src' => $img_src, 'title' => lang($action['img']))));
        }
    }
}
