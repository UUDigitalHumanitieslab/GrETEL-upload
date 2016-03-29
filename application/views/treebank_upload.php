<h2><?=$page_title; ?></h2>

<?php if ($this->session->flashdata('message')) { ?>
	<div class="success">
		<?=$this->session->flashdata('message'); ?>
	</div>
<?php } ?>

<?=form_open_multipart($action, array('class' => 'pure-form pure-form-aligned')); ?>

<p>
	A treebank (in this case) consists of a number of archived folders (<em>.zip</em>), 
	with each folder containing one of the below:
</p>
<ul>
	<li>sentences parsed by Alpino (<em>*.xml</em>-files)</li>
	<li>plain-text files (with extension <em>*.txt</em>), with a sentence on each line.</li>
</ul>
<p>
	Choose an appropriate title for your treebank and upload your <em>.zip</em>-file below.
	Then, please set the correct parse attributes.
</p>

<?=form_fieldset($page_title); ?>
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

<script>
$(function() {
	$('input[name="is_sent_tokenised"').prop('checked', true).prop('disabled', true);
});
</script>