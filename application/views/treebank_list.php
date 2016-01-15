<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<table class="pure-table">
	<thead>
		<tr>
			<th><?=lang('title'); ?></th>
			<th><?=lang('uploaded_by'); ?></th>
			<th><?=lang('uploaded_at'); ?></th>
			<th><?=lang('processed_at'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($treebanks as $treebank): ?>
		<tr>
			<td><?=anchor('treebank/show/' . $treebank->title, $treebank->title); ?></td>
			<td><?=$treebank->email; ?></td>
			<td><?=$treebank->uploaded; ?></td>
			<td><?=$treebank->processed; ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
