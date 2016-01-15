<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<?=form_open_multipart($action, array('class' => 'pure-form pure-form-aligned')); ?>

<?=form_fieldset($page_title); ?>

<?=form_input_and_label('title'); ?>
<div class="pure-control-group">
<?=form_label(lang('file'), 'treebank'); ?>
<input type="file" name="treebank" size="20" class="pure-input-rounded" />
<?=form_error('treebank'); ?>
</div>
<?=form_single_checkbox_and_label('public', TRUE, TRUE); ?>

<?=form_controls(); ?>
<?=form_fieldset_close(); ?>
<?=form_close(); ?>
