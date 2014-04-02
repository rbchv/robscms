
<div class="row">
	<div class="span12">
    	<div id="usersview" class="generalBgWrapperDiv">
			<h1><?php echo __('User admin'); ?></h1>

<?php

	$opcionesPermisos = $this->Postpermission->getPermissionArray();

	echo '<table id="usertable" class="table table-striped">';
	echo '<tr>';
	echo '<th>Id</td>';
	echo '<th>' . __('Name') . '</td>';
	echo '<th>' . __('Lastname') . '</td>';
	echo '<th>' . __('Email') . '</td>';
	echo '<th>' . __('Permissions') . '</td>';
	echo '<th>' . __('Created') . '</td>';
	echo '</tr>';

	foreach($allUsers as $thisUser)
	{
		echo $this->Form->create('User', array('id' => 'user' . $thisUser['User']['id'], 'action' => 'update'));
		echo $this->Form->input('id', array('type' => 'hidden', 'value' => $thisUser['User']['id']));

		echo '<tr>';
		echo '<td>' . $thisUser['User']['id'] . '</td>';
		echo '<td>' . $thisUser['User']['firstname'] . '</td>';
		echo '<td>' . $thisUser['User']['lastname'] . '</td>';
		echo '<td>' . $thisUser['User']['email'] . '</td>';
		echo '<td class="permcell">';
			echo '<span class="permtext">' . $opcionesPermisos[$thisUser['User']['permissions']] . '</span>';
			echo '<span class="permchange">';
				echo $this->Form->input('permissions', array('class' => 'input-medium', 'onChange' => 'submitPermChange(this);', 'type'=>'select', 'label' => false, 'options' => $opcionesPermisos, 'default' => $thisUser['User']['permissions']));
			echo '</span>';
			echo '<span class="permsaving muted">' . $this->Html->image('loading_small.gif') . ' Salvando</span>';
		echo '</td>';
		echo '<td>' . ucfirst($this->Friendlydate->convert($thisUser['User']['created'])) . '</td>';
		echo '</tr>';

		echo $this->Form->end();
	}

	echo '</table>';

?>
		</div>
	</div>
</div>
