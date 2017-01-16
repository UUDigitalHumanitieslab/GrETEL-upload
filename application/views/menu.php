<div class="pure-menu pure-menu-horizontal">
	<ul class="pure-menu-list">
	<?php 
		if ($this->session->userdata('logged_in'))
		{
			$links = array(
				array('url' => 'upload', 'name' => 'upload_treebank'),
				array('url' => 'treebank/user/' . current_user_id(), 'name' => 'my_treebanks'),
				array('url' => 'treebank', 'name' => 'public_treebanks'),
				array('url' => GRETEL_URL, 'name' => 'GrETEL'),
				array('url' => 'logout', 'name' => 'logout'),
			);
		}
		else
		{
			$links = array(
				array('url' => 'login', 'name' => 'login'),
				array('url' => 'treebank', 'name' => 'public_treebanks'),
				array('url' => GRETEL_URL, 'name' => 'GrETEL'),
			);
		}

		foreach ($links as $link)
		{
			echo '<li class="pure-menu-item">';
			echo anchor($link['url'], lang($link['name']), 'class="pure-menu-link"');
			echo '</li>';
		}
	?>
	</ul>
</div>
