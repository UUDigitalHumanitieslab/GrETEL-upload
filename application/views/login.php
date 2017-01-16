<?=heading($page_title, 2); ?>

<p class="info">
	<?=lang('login_help'); ?>
</p>

<?php if (validation_errors()) { ?>
<div class="failed">
	<?=validation_errors(); ?>
</div>
<?php } ?>

<?=form_open($action, array('class' => 'pure-form')); ?>

<?=form_input('username', '', array('placeholder' => lang('username'))); ?>
<?=form_password('password', '', array('placeholder' => lang('password'))); ?>
<?=form_submit('submit', lang('login'), array('class' => 'pure-button pure-button-primary')); ?>

<?=form_close(); ?>

<p>
	<?=lang('login_warning'); ?>
</p>
<?php if (GUEST_USERNAME) { ?>
<p>
	<?=sprintf(lang('login_guest'), site_url('login/guest')); ?>
</p>
<?php } ?>
