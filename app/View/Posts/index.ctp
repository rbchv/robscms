

<div class="row">
	<div class="span12" >
    	<div id="postsindex">



<?php

//Create "about me" div.
$aboutMeDiv = '';
if(isset($meData))
{
	if($this->Session->check('id'))
	{
		$aboutMeDiv = '<div class="alert alert-success aboutMeDiv">' . $meData['Me']['about_loggedin'] . '</div>';
	} else
	{
		$aboutMeDiv = '<div class="alert alert-success aboutMeDiv">' . $meData['Me']['about_loggedout'] . '</div>';
	}
}



if(count($visiblePosts) == 0)
{
	if(isset($id))
	{
		echo '<div class="alert alert-block"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>' . __('No posts found') . '!</h4></div>';
	}
	echo $aboutMeDiv;

} else
{

	$pic_upload_directory = Configure::read('PIC_UPLOAD_DIRECTORY');

	$allPosts = array();
	$cnt = 0;

	foreach($visiblePosts as $thisPost)
	{
		if($thisPost['Post']['filename'])
		{
			//Is a picture!
			$showText = '';
			$showText .= '<img class="robsthumbnail" alt="" src="/' . IMAGES_URL . $pic_upload_directory . '/400/' . $thisPost['Post']['filename'] . '" />';
			$showText .= ($thisPost['Post']['title'] == '' ? '' : '<p class="caption">' . $thisPost['Post']['title'] . '</p>');

			$linkData = $this->Html->url(array('controller' => 'posts', 'action' => 'view', $thisPost['Post']['id']));
		} else
		{
			//Is a post without picture
			$thisTitle = $thisPost['Post']['title'];
			$thisText = $thisPost['Post']['text'];
			$thisIsQuote = $thisPost['Post']['isQuote'];

			$showText = '';
			$showText .= ( $thisTitle == '' ? '' : '<h2>' . $thisTitle . '</h2>');
			$showText .= ( $thisText == '' ? '' : ($thisIsQuote ? $this->Html->image('quote.png' ,array('class' => 'smallquote')) : '') . substr(strip_tags(preg_replace("/<a href.+?<\/a>/i", "", $thisText)), 0, 300) . (strlen(strip_tags($thisText)) > 300 ? '...' : ''));

			$linkData = $this->Html->url(array('controller' => 'posts', 'action' => 'view', $thisPost['Post']['id']));
		}

		$allPosts[$cnt++] = '<div data-url="' . $linkData . '" class="postDiv">' . $showText . '</div>';
	}

	//Add "About me" div to the beginning of the array
	if($aboutMeDiv)
	{
		array_unshift($allPosts, $aboutMeDiv);
	}


	//Create mobile version (all in one column)
	echo '<div class="row visible-phone">';
	echo '<div class="span12">';
	echo implode(' ', $allPosts);
	echo '</div></div>';


	//Create desktop version
	$maxColumns = 4;
	$allCols = array($maxColumns);
	$colCnt = 0;

	//Init cols
	for($x = 0; $x < $maxColumns; $x++)
	{
		$allCols[$x] = '';
	}

	foreach($allPosts as $thisPost)
	{
		$allCols[$colCnt++] .= $thisPost;
		if($colCnt == $maxColumns)
		{
			$colCnt = 0;
		}
	}

	$spanNum = 12 / $maxColumns;
	echo '<div class="row hidden-phone">';
	foreach($allCols as $thisCol)
	{
		echo '<div class="span'.$spanNum.'">' . $thisCol . '</div>';
	}
	echo '</div>';
}
?>

<script>
$(document).ready(function() { $('div.postDiv').click(function(e) { if($(this).data('url')) { window.location.href = $(this).data('url'); } }); });
</script>
