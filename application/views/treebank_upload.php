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
	<li><abbr title="<?=lang('help_plain_text'); ?>">plain-text files</abbr> (with extension <em>*.txt</em>)</li>
</ul>
<p>
	Choose an appropriate title for your treebank and upload your <em>.zip</em>-file below.
	Then, please set the correct parse attributes for your files (they will not be detected automatically).
</p>

<?=form_fieldset($page_title); ?>
<?=form_input_and_label('title'); ?>
<div class="pure-control-group">
<?=form_label(lang('file'), 'treebank'); ?>
<input type="file" name="treebank" size="20" class="pure-input-rounded" />
<?=form_error('treebank'); ?>
</div>
<?=form_single_checkbox_and_label('public', '1', '1'); ?>
<?=form_help('help_publicly_available'); ?>
<?=form_fieldset_close(); ?>

<?=form_fieldset(lang('parse_flags')); ?>
<?=form_single_checkbox_and_label('is_txt', '1'); ?>
<?=form_help('help_is_txt'); ?>
<?=form_single_checkbox_and_label('is_sent_tokenised', '1'); ?>
<?=form_help('help_is_sent_tokenised'); ?>
<?=form_single_checkbox_and_label('is_word_tokenised', '1'); ?>
<?=form_help('help_is_word_tokenised'); ?>
<?=form_single_checkbox_and_label('has_labels', '1'); ?>
<?=form_help('help_has_labels'); ?>

<?=form_controls(); ?>
<?=form_fieldset_close(); ?>
<?=form_close(); ?>

<script>
$(document).ready(function() {
	// Creates a tooltip for each <abbr> element
	$('abbr[title]').qtip({
		hide: {
			fixed: true,
			delay: 300,
		}
	});

	// Move help divs to the end of the pure-control-group 
	$('.pure-control-group').each(function() {
		$(this).append($(this).next('.help'));
	});

	// Creates a tooltip for each help image
    $('.help img').each(function() {
        $(this).qtip({
            content: $(this).next('.tooltiptext'),
            hide: {
                fixed: true,
                delay: 500,
            }
        });
    });

    // Hides all tooltiptext-spans
    $('.tooltiptext').hide();
});
</script>
