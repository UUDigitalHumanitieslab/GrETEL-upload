<?=doctype(); ?>
<html lang="en">
<head>
<base href="<?=base_url(); ?>">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=lang('site_title'); ?></title>
<script src="//code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="//cdn.jsdelivr.net/qtip2/3.0.3/jquery.qtip.min.js"></script>
<?=link_tag('//cdn.jsdelivr.net/qtip2/3.0.3/jquery.qtip.min.css'); ?>
<?=link_tag('//yui.yahooapis.com/pure/0.6.0/pure-min.css'); ?>
<?=link_tag('//fonts.googleapis.com/css?family=Oswald:400,300,700|Carrois+Gothic'); ?>
<?=link_tag('css/style.css'); ?>

<div class="pure-g">
	<div class="pure-u-1-8"></div>
	<div id="wrapper" class="pure-u-3-4">
		<img id="header-img" src="images/uu-header.png">
		<?=heading(lang('site_title'), 1); ?>
		<?=$this->load->view('menu', NULL, TRUE); ?>
