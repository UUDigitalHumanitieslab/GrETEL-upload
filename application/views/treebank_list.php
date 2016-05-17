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
			<?php if ($this->session->userdata('logged_in')) { ?>
			<th><?=lang('actions'); ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($treebanks as $treebank): ?>
		<tr>
			<td><?=anchor('treebank/show/' . $treebank->title, $treebank->title); ?></td>
			<td><?=$treebank->email; ?></td>
			<td><?=$treebank->uploaded; ?></td>
			<td><?=$treebank->processed; ?></td>
			<?php if ($this->session->userdata('logged_in')) { ?>
				<td class="actions">
				<?php
					if ($treebank->user_id == $this->session->userdata('user_id'))
					{
						$actions = array(
							array('url' => 'treebank/change_access/' . $treebank->id, 'title' => ($treebank->public ? 'make_private' : 'make_public')),
							array('url' => 'treebank/delete/' . $treebank->id, 'title' => 'delete'),
						);
						if (!$treebank->processed) {
							array_unshift($actions,
								array('url' => 'cron/process/by_id/' . $treebank->id, 'title' => 'process')
							);
						}
						foreach ($actions as $action) {
							echo anchor($action['url'], lang($action['title']));
						}
					}
				?>
				</td>
			<?php } ?>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php } else { ?>
<p>
	No treebanks yet. Consider uploading one via <?=anchor('upload', 'the upload functionality'); ?>.
</p>
<?php } ?>
