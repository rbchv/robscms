<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title><?php echo Configure::read('SITE_NAME_SHORT') . ($title_for_layout == '' ? '' : ' | ' . $title_for_layout); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('bootstrap-responsive.min');
		echo $this->Html->css('robscms');
		echo "<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>";
		echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
		echo $this->Html->script('bootstrap.min');

		//Conditional Includes
		if($this->params['controller'] == 'posts')
		{
			if( ($this->params['action'] == 'view') || ($this->params['action'] == 'edit') )
			{
				echo $this->Html->script('http://maps.googleapis.com/maps/api/js?key=AIzaSyDfbeL0PQhyLRhX7Pbei-OddQRoh7elrMM&sensor=false&libraries=geometry');
				echo $this->Html->script('map');
			}

			if($this->params['action'] == 'view')
			{
				echo $this->Html->script('postsview');
			}

			if($this->params['action'] == 'map')
			{
				echo $this->Html->script('http://maps.googleapis.com/maps/api/js?key=AIzaSyDfbeL0PQhyLRhX7Pbei-OddQRoh7elrMM&sensor=false&libraries=geometry');
				echo $this->Html->script('postsmap');
			}
		}
		if($this->params['controller'] == 'users')
		{
			if($this->params['action'] == 'view')
			{
				echo $this->Html->script('usersview');
			}
		}
	?>
</head>
<body>

<?php

if(isset($bgImagePath))
{
	echo '
		<style>
		body
		{ 
			background: #999 url(\'' . $bgImagePath . '\') no-repeat center center fixed; 
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		</style>';
}
?>

<div id="toppixelpattern"></div>

<?php echo $this->element('header'); ?>

<div class="container" id="mainlayoutcontainer">
	<div class="row">
    	<div class="span12">
        	<?php echo $this->Session->flash(); ?>
		</div>
    </div>
    <?php echo $content_for_layout; ?>
</div>

<?php echo $this->element('footer'); ?>

</body>
</html>
