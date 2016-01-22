<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<?php if ($treebanks) { ?>
<table class="pure-table">
	<thead>
		<tr>
			<th><?=lang('title'); ?></th>
			<th><?=lang('uploaded_by'); ?></th>
			<th><?=lang('uploaded_at'); ?></th>
			<th><?=lang('processed_at'); ?></th>
			<th><?=lang('actions'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($treebanks as $treebank): ?>
		<tr>
			<td><?=anchor('treebank/show/' . $treebank->title, $treebank->title); ?></td>
			<td><?=$treebank->email; ?></td>
			<td><?=$treebank->uploaded; ?></td>
			<td><?=$treebank->processed; ?></td>
			<td>
				<?php if (!$treebank->processed) { echo anchor('cron/process/by_id/' . $treebank->id, 'Process'); echo ' |'; } ?>
				<?=anchor('treebank/change_access/' . $treebank->id, $treebank->public ? 'Make private' : 'Make public'); ?> |
				<?=anchor('treebank/delete/' . $treebank->id, 'Delete'); ?>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php } else { ?>
<p>
	No treebanks yet. Consider uploading one via <?=anchor('upload', 'the upload functionality'); ?>.
</p>
<?php } ?>
