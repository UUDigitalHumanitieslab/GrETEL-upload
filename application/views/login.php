<?=heading($page_title, 2); ?>

<p class="info">
	<?=lang('login_help'); ?>
</p>

<?=form_open($action, array('class' => 'pure-form')); ?>

<?=form_input('username', '', array('placeholder' => lang('username'))); ?>
<?=form_password('password', '', array('placeholder' => lang('password'))); ?>
<?=form_submit('submit', lang('login'), array('class' => 'pure-button pure-button-primary')); ?>

<?=form_close(); ?>
<?=validation_errors(); ?>

<p>
	<?=lang('login_warning'); ?>
</p>
