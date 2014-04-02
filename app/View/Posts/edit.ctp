
<script>
var allowMapEdit = true;
</script>



<div class="row">
	<div class="span12">
    	<div id="postsedit" class="generalBgWrapperDiv">
			<h1><?php echo __('Create/edit post'); ?></h1>



<?php

echo $this->Form->create('Post', array('action' => 'edit', 'type' => 'file'));
echo $this->Form->input('id', array('type' => 'hidden'));

echo $this->Form->input('title', array('type' => 'text', 'label' => __('Title'), 'div' => array('id' => 'asdfpostInputTitleDiv')));
echo $this->Form->input('text', array('type' => 'textarea', 'label' => __('Text'), 'div' => array('id' => 'asdfpostInputTextDiv')));



echo '<div class="row-fluid" id="horizontalInputDiv">';
echo '<div class="span5">';


if(isset($this->request->data['Post']['filename']) && ($this->request->data['Post']['filename'] != ''))
{
	echo '<div id="currentPic">';
	echo __('Current picture') . '<br /><br />';
	$picUploadDirectory = Configure::read('PIC_UPLOAD_DIRECTORY');
	echo '<img src="/' . IMAGES_URL . $picUploadDirectory . '/200/' . $this->request->data['Post']['filename'] . '" /><br />';
	echo $this->Html->link(__('Delete'), '#', array('id' => "btnBorrar"));
	echo '</div>';
}

echo $this->Form->input('filename', array('type' => 'hidden'));
echo $this->Form->input('pic', array('type' => 'file', 'label' => __('Select a file to upload'), 'div' => array('id' => 'postInputFileDiv')));

echo $this->Form->input('isBg', array('type' => 'checkbox', 'label' => __('Use as site background'), 'div' => array('id' => 'postInputIsBgDiv')));
echo $this->Form->input('isQuote', array('type' => 'checkbox', 'label' => __('Is a quote'), 'div' => array('id' => 'postInputIsQuoteDiv')));
echo $this->Form->input('isStatus', array('type' => 'checkbox', 'label' => __('Is a status update'), 'div' => array('id' => 'postInputIsStatusDiv')));

echo '<br />';
$opcionesPermisos = $this->Postpermission->getPermissionArray();
echo $this->Form->input('permissions', array('type' => 'select', 'label' => __('This post\'s permissions:'), 'options' => $opcionesPermisos, 'default' => $this->Postpermission->getMaxPermission()));


echo '</div>';


echo '<div class="span7">';

echo __('Mark location on the map') . '<br /><br />';
echo $this->Form->input('lat', array('type' => 'hidden'));
echo $this->Form->input('long', array('type' => 'hidden'));

echo '<div id="map-canvas"></div>';

echo '</div>';
echo '</div>';
echo '<br/><br />';

echo $this->Form->submit(__('Save'), array('class' => 'btn btn-inverse'));
echo $this->Form->end();

?>

		</div>
	</div>
</div>





<script>

if($('div#currentPic').length)
{
	$('div#postInputFileDiv').css('display', 'none');
}


//div#postInputFileDiv
$('#btnBorrar').click(function(e)
{
    if(confirm('<?php echo __('Do you want to delete this picture') . '?'; ?>'))
	{
		$('#PostFilename').val('');
		$('div#currentPic').css('display', 'none');
		$('div#postInputFileDiv').css('display', 'block');
	}
	return false;
});

</script>

