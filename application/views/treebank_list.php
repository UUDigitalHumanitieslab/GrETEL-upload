<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>
<?php if ($this->session->flashdata('error')) { ?>
	<div class="error">
		<?=$this->session->flashdata('error'); ?>
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
				<?=treebank_actions($treebank->id); ?>
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
