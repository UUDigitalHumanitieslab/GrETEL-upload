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
			<th><?=lang('nr_sentences'); ?></th>
			<th><?=lang('nr_words'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($components as $component): ?>
		<tr>
			<td><?=$component->title; ?></td>
			<td><?=$component->nr_sentences; ?></td>
			<td><?=$component->nr_words; ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?=anchor('treebank', lang('back')); ?>
