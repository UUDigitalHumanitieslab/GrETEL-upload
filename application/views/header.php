<?=doctype(); ?>
<html lang="en">
<head>
<base href="<?=base_url(); ?>">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=lang('site_title'); ?></title>
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<?=link_tag('http://yui.yahooapis.com/pure/0.6.0/pure-min.css'); ?>
<?=link_tag('http://fonts.googleapis.com/css?family=Oswald:400,300,700|Carrois+Gothic'); ?>
<?=link_tag('css/style.css'); ?>

<div class="pure-g">
	<div class="pure-u-1-8"></div>
	<div id="wrapper" class="pure-u-3-4">
		<img id="header-img" src="images/uu-header.png">
		<?=heading(lang('site_title'), 1); ?>
		<?=$this->load->view('menu', NULL, TRUE); ?>
