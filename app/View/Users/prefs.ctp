
<div class="row">
	<div class="span12">
    	<div id="usersprefs" class="generalBgWrapperDiv">
			<h1><?php echo __('My preferences'); ?></h1>
			<br />

			<?php
				//Do this to get prefilled data.
				$this->request->data['User'] = $this->request->data['Pref'];

				$opcionesEmail = array(__('Daily email'), __('Weekly email'), __('Never'));

				$langArray = Configure::read('AVAILABLE_LANGS');
				$opcionesLang = array();
				foreach($langArray as $thisLang)
				{
					$opcionesLang[] = $thisLang[1];
				}

				echo $this->Form->create('User', array('action' => 'prefs'));
				echo $this->Form->input('emailfreq', array('type' => 'select', 'label' => __('Email frequency'), 'options' => $opcionesEmail, 'default' => 0));

				echo '<br />';
				echo $this->Form->input('lang', array('type' => 'select', 'label' => __('Language preference'), 'options' => $opcionesLang, 'default' => 0));

				echo '<br />';
				echo $this->Form->submit(__('Save'), array('class' => 'btn btn-inverse'));
				echo $this->Form->end();
			?>

		</div>
	</div>
</div>
