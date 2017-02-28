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
			<th><?=lang('facet'); ?></th>
			<th><?=lang('show'); ?></th>
		</tr>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($metadata as $m): ?>
		<tr>
			<td><?=$m->field; ?></td>
			<td><?=$m->type; ?></td>
			<td><?=$m->min_value; ?></td>
			<td><?=$m->max_value; ?></td>
			<td>
				<?php
					if ($this->session->userdata('logged_in'))
					{
						echo form_open('metadata/update_facet/' . $m->id);
						echo form_dropdown('facet', facet_options(), $m->facet);
						echo form_close();
					}
					else
					{
						echo lang('facet-' . $m->facet);
					}
				?>
			</td>
			<td>
				<?php
					$src = $m->show === '1' ? 'images/tick.png' : 'images/cross.png';
					if ($this->session->userdata('logged_in'))
					{
						$url = 'metadata/update_shown/' . $m->id . '/' . ($m->show === '1' ? '0' : '1');
						echo anchor($url, img(array('src' => $src)));
					}
					else
					{
						echo img(array('src' => $src));
					}
				?>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php } ?>

<p>
	<?=anchor($this->agent->referrer(), lang('back')); ?>
</p>

<script>
$(function() {
	$('select').on('change', function() {
		$(this).closest('form').submit();
	});
});
</script>
