<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<?=form_open_multipart($action, array('class' => 'pure-form pure-form-aligned')); ?>

<?=form_fieldset($page_title); ?>
<p>
	A treebank (in this case) consists of a number of folders, with each folder containing sentences parsed by Alpino (*.xml-files).
	Choose an appropriate filename and upload your treebank below.
</p>
<?=form_input_and_label('title'); ?>
<div class="pure-control-group">
<?=form_label(lang('file'), 'treebank'); ?>
<input type="file" name="treebank" size="20" class="pure-input-rounded" />
<?=form_error('treebank'); ?>
</div>
<?=form_single_checkbox_and_label('public', '1', '1'); ?>
<?=form_fieldset_close(); ?>
<?=form_fieldset(lang('parse_flags')); ?>
<?=form_single_checkbox_and_label('is_txt', '1'); ?>
<?=form_single_checkbox_and_label('is_sent_tokenised', '1'); ?>
<?=form_single_checkbox_and_label('is_word_tokenised', '1'); ?>
<?=form_single_checkbox_and_label('has_labels', '1'); ?>

<?=form_controls(); ?>
<?=form_fieldset_close(); ?>
<?=form_close(); ?>
