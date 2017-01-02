<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<h3><?=lang('log'); ?></h3>
<table class="pure-table">
	<thead>
		<tr>
			<th><?=lang('time'); ?></th>
			<th><?=lang('level'); ?></th>
			<th><?=lang('message'); ?></th>
			<th><?=lang('filename'); ?></th>
			<th><?=lang('linenumber'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($importlogs as $importlog): ?>
		<tr>
			<td><?=$importlog->time_logged; ?></td>
			<td><?=$importlog->level; ?></td>
			<td><?=$importlog->body; ?></td>
			<td><?=$importlog->filename; ?></td>
			<td><?=$importlog->linenumber; ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
