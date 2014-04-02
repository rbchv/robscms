

<div class="row">
	<div class="span12">
    	<div id="meview" class="generalBgWrapperDiv">
			<h1><?php echo __('About me'); ?></h1>
			<?php
				echo $this->Form->create('Me', array('action' => 'index', 'method' => 'post'));
				echo $this->Form->input('id', array('type' => 'hidden'));
				echo $this->Form->input('about_loggedin', array('type' => 'textarea', 'label' => __('About me - Logged in'), 'div' => array('id' => '')));
				echo $this->Form->input('about_loggedout', array('type' => 'textarea', 'label' => __('About me - Not logged in'), 'div' => array('id' => '')));
				echo $this->Form->submit(__('Save'), array('class' => 'btn btn-inverse'));
				echo $this->Form->end();
			?>
		</div>
	</div>
</div>

