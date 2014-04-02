
<div class="row">
	<div class="span4 offset4 well">
    	<div id="userslogin">
            <h1><?php echo __('Welcome'); ?></h1>
            <?php echo __('You can log in with your Google') . ' ' . (Configure::read('FB_APP_ENABLED') ? __('or Facebook') : '') . ' ' . __('account'); ?>.<br />

<?php
	echo $this->Html->image('signinwithgoogle.png', array('class' => 'signinbutton', 'alt' => __('Sign in with Google'), 'title' => __('Sign in with Google'), 'width' => '199px',
		'url' => array('controller' => 'users', 'action' => 'google')));

    if(Configure::read('FB_APP_ENABLED'))
    {
    	echo $this->Html->image('loginwithfacebook.png', array('class' => 'signinbutton', 'alt' => __('Sign in with Facebook'), 'title' => __('Sign in with Facebook'), 'width' => '199px',
    		'url' => array('controller' => 'users', 'action' => 'facebook')));
    }
?>
            <hr />
            <p class="muted"><?php echo __('If this is the first time you log in, you must wait for your permissions to be approved'); ?>.</p>
            <?php echo $this->Html->link(__('Back'), '/'); ?>
        </div>
	</div>
</div>
