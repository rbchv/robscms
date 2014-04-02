
<style id="popoverStyle"></style>



<div class="row">
	<div class="span12">
		<div id="postsview" class="generalBgWrapperDiv">

<?php

//This is used below to generate the JS url
$postId = -1;

if(count($thisPost) == 0)
{
	echo '<div class="alert alert-block"><h4>' . __('Error') . '!</h4>' . __('No post found or you don\'t have sufficient permissions to view this post') . '. <a href="/">' . __('Back') . '</a></div>';
} else
{

	$postId = $thisPost['Post']['id'];
	$permissions = $thisPost['Post']['permissions'];
	$title = $thisPost['Post']['title'];
	$text = $thisPost['Post']['text'];
	$filename = $thisPost['Post']['filename'];
	$isQuote = $thisPost['Post']['isQuote'];
	$isStatus = $thisPost['Post']['isStatus'];
	$created = $thisPost['Post']['created'];
	$modified = $thisPost['Post']['modified'];
	$key = $thisPost['Post']['key'];
	$exif = ($thisPost['Post']['exif'] ? $thisPost['Post']['exif'] : __('No data'));
	$latLong = 'No data';
	if($thisPost['Post']['lat'])
	{
		$latLong = '<a href="' . $this->Html->url(array('action' => 'map')) . '">';
		$latLong .= '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $thisPost['Post']['lat'] . ',' . $thisPost['Post']['long'] . '&markers=color:red|label:none|' . $thisPost['Post']['lat'] . ',' . $thisPost['Post']['long'] . '&zoom=4&size=400x400&maptype=roadmap&sensor=false" />';
		$latLong .= '</a>';
	}

	$this->set('title_for_layout', $title);


	//Close button
	echo $this->Html->image('close.png', array('id' => 'closebtn', 'onClick' => 'closeFunction(null);', 'alt' => __('Close'), 'title' => __('Close')));


	if($filename)
	{
		//Is picture
		$picUploadDirectory = Configure::read('PIC_UPLOAD_DIRECTORY');
		echo '<img id="picDetail" src="/' . IMAGES_URL . $picUploadDirectory . '/1200/' . $filename . '" alt="' . $title . '" title="' . $title . '" />';

		//Title and text
		echo '<p id="picDetailCaption">';
		echo $this->Html->image('mapicon.png', array('id' => 'showmapbtn', 'data-html' => 'true', 'data-content' => $latLong));
		echo $this->Html->image('cameraicon.png', array('id' => 'showexifbtn', 'data-html' => 'true', 'data-content' => $exif));

		echo '<span id="picTitle">' . $title . '</span>';
		if($title && $text)
		{
			echo ' - ';
		}
		echo ($text == '' ? '' : $text);
		echo '</p>';

		echo '<p class="muted" id="createdon">' . __('Uploaded') . ' ' . $this->Friendlydate->convert($created) . '.</p>';
	} else
	{
		//Is text post
		if($title != '')
		{
			echo '<h2>' . $title . '</h2>';
		} else
		{
			echo '<br />';
		}

		echo '<p>' . ($isQuote ? $this->Html->image('quote.png' ,array('class' => 'smallquote')) : '') . nl2br($text) . '</p>';
		echo '<p class="muted" id="createdon">' . __('Written') . ' ' . $this->Friendlydate->convert($created) . '.</p>';

	}

	//Links
	echo '<p id="speciallinks">';
	echo '<a href="/">' . __('Back') . '</a>';
	if($this->Session->read('permissions') == $this->Postpermission->getMaxPermission())
	{
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;' . $this->Html->link(__('Edit'), array('controller' => 'posts', 'action' => 'edit', $postId));
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;' . $this->Html->link(__('Delete'), array('controller' => 'posts', 'action' => 'delete', $postId), array(), __('Do you want to delete this post?'));
		$permsArray = $this->Postpermission->getPermissionArray();
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;' . '<span class="muted">' . __('Permissions') . ': ' . $permsArray[$permissions] . '</span>';
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;' . '<span class="muted">' . __('Created') . ': ' . $created . '</span>';
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;' . '<span class="muted">' . __('Modified') . ': ' . $modified . '</span>';

		//Setup key icon for use
		$thisUrl = FULL_BASE_URL . $this->Html->url(array('controller' => 'posts', 'action' => 'view', $postId));
		$keyPopup = '<b>Access key:</b><br/><pre id="accessKeyPre">' . $thisUrl . DS . $key . '</pre>' . 
			$this->Html->image('refreshicon.png', array('id' => 'refreshbtn')) . 
			'<span class="muted" id="keymessagearea"></span>' ;
		echo $this->Html->image('keyicon.png', array('id' => 'keybtn', 'data-html' => 'true', 'data-content' => $keyPopup));
	}
	echo '</p>';



	if(count($thisPost['Comment']) == 0)
	{
		echo '<h4>' . __('There are no comments') . '</h4>';
	} else
	{
		echo '<h4>' . __('There are') . ' ' . count($thisPost['Comment']) . ' ' . __('comment') . (count($thisPost['Comment']) == 1 ? '' : 's') . '</h4>';
	}



	echo '<div class="specificcomment">';
	if(isset($id))
	{
		//Is logged in... show form.
		echo $this->Html->image('users/' . $id . '.jpg', array('class' => 'userImg'));
		echo $this->Form->create('Comment', array('action' => 'write'));
		echo $this->Form->input('Post.id', array('type' => 'hidden', 'value' => $thisPost['Post']['id']));
		echo $this->Form->input('text', array('after' => '<button class="btn" type="button" onClick="submit();">' . __(
			'Save comment') . '</button>', 'type' => 'text', 'class' => 'input-xxlarge', 'placeholder' => __('Write your comment here') . '...', 'label' => false, 'div' => array('class' => 'input-append')));
		echo $this->Form->end();
	} else
	{
		echo $this->Html->link(__('Log in to your account'), array('controller' => 'users', 'action' => 'login')) . ' ' . __('to leave a comment') . '.<br /><br />';
	}
	echo '</div>';


	if(count($thisPost['Comment']) > 0)
	{
		foreach($thisPost['Comment'] as $thisComment)
		{
			echo '<div class="specificcomment">';
			echo $this->Html->image('users/' . $thisComment['User']['id'] . '.jpg', array('class' => 'userImg', 'alt' => $thisComment['User']['firstname']));
			echo '<small class="muted">' . __('Written') . ' ' . $this->Friendlydate->convert($thisComment['created']) . ' ' . __('by') . ' ' . $thisComment['User']['firstname'] .'.</small>';
			echo '<p>';
			echo $thisComment['text'];

			if($this->Session->read('permissions') == $this->Postpermission->getMaxPermission())
			{
				echo $this->Html->link(
					$this->Html->image('delete.png', array('id' => 'deleteCommentImg')),
					array('controller' => 'comments', 'action' => 'delete', $thisComment['id']),
					array('escape' => false),
					__('Delete this comment') . '?');
			}

			echo '</p>';
			echo '</div>';
		}
	}
}
?>

		</div>
	</div>
</div>


<script>
	var creatingNewKeyText = '<?php echo __('Saving'); ?>';
	var successNewKeyText = '<?php echo __('Success') . "!"; ?>';
	var errorNewKeyText = '<?php echo __('Error'); ?>';
	var creatingNewKeyUrl = '<?php echo FULL_BASE_URL . $this->Html->url(array('controller' => 'posts', 'action' => 'newkey', $postId)); ?>';
</script>




