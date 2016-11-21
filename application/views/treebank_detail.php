<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<h3><?=lang('components'); ?></h3>
<table class="pure-table">
	<thead>
		<tr>
			<th><?=lang('slug'); ?></th>
			<th><?=lang('title'); ?></th>
			<th><?=lang('nr_sentences'); ?></th>
			<th><?=lang('nr_words'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($components as $component): ?>
		<tr>
			<td><?=$component->slug; ?></td>
			<td><?=$component->title; ?></td>
			<td><?=number_format($component->nr_sentences); ?></td>
			<td><?=number_format($component->nr_words); ?></td>
		</tr>
		<?php endforeach ?>
		<tr class="pure-table-odd">
			<td></td>
			<td></td>
			<td><strong><?=number_format($total_sentences); ?></strong></td>
			<td><strong><?=number_format($total_words); ?></strong></td>
		</tr>
	</tbody>
</table>

<?php if ($metadata) { ?>
<h3><?=lang('metadata'); ?></h3>
<table class="pure-table">
	<thead>
		<tr>
			<th><?=lang('field'); ?></th>
			<th><?=lang('type'); ?></th>
			<th><?=lang('min_value'); ?></th>
			<th><?=lang('max_value'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($metadata as $m): ?>
		<tr>
			<td><?=$m->field; ?></td>
			<td><?=$m->type; ?></td>
			<td><?=$m->min_value; ?></td>
			<td><?=$m->max_value; ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php } ?>

<p>
	<?=anchor('treebank', lang('back')); ?>
</p>
