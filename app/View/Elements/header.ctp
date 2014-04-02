
<div class="navbar navbar-inverse">
	<div class="navbar-inner">
    	<?php echo $this->Html->link(Configure::read('SITE_NAME_SHORT'), array('controller' => 'posts', 'action' => 'index'), array('class' => 'brand')); ?>
		<ul class="nav pull-right">
			<li><?php echo $this->Html->link(__('Map'), array('controller' => 'posts', 'action' => 'map')); ?></li>

<?php
if(isset($id))
{
	echo '<li>' . $this->Html->image("users/$id.jpg", array('id' => 'profilePicHeader')) . '</li>';

	if($permissions == $this->Postpermission->getMaxPermission())
	{
		echo '
				<li id="fat-menu" class="dropdown">
					<a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">' . __('My account') . '<b class="caret"></b></a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
						<li>' . $this->Html->link(__('Create new post'), array('controller' => 'posts', 'action' => 'edit'), array('tabindex' => '-1')) . '</li>
						<li>' . $this->Html->link(__('Manage users'), array('controller' => 'users', 'action' => 'view'), array('tabindex' => '-1')) . '</li>
						<li class="divider"></li>' .
						'<li>' . $this->Html->link(__('My preferences'), array('controller' => 'users', 'action' => 'prefs'), array('tabindex' => '-1')) . '</li>' .
						'<li>' . $this->Html->link(__('About me'), array('controller' => 'me', 'action' => 'index'), array('tabindex' => '-1')) . '</li>' .
						'<li class="divider"></li>
						<li>' . $this->Html->link(__('Log out'), array('controller' => 'users', 'action' => 'logout'), array('tabindex' => '-1')) . '</li>
					</ul>
				</li>';
	} else
	{
		echo '<li>' . $this->Html->link(__('My preferences'), array('controller' => 'users', 'action' => 'prefs'), array('tabindex' => '-1')) . '</li>';
		echo '<li>' . $this->Html->link(__('Log out'), array('controller' => 'users', 'action' => 'logout'), array('tabindex' => '-1')) . '</li>';
	}
} else
{
	echo '<li>' . $this->Html->link(__('Log in'), array('controller' => 'users', 'action' => 'login'), array('tabindex' => '-1')) . '</li>';
}
?>

		</ul>
	</div>
</div>
