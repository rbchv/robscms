
<script>
var picMarkers = new Array();

<?php
//Add markers to the JS code
$cnt = 0;
foreach($visiblePosts as $thisPost)
{
	if($thisPost['Post']['lat'])
	{
		echo 'picMarkers[' . $cnt . '] = new Array("' .
			$thisPost['Post']['lat'] . '", "' .
			$thisPost['Post']['long'] . '", "' .
			$this->Html->url(array('controller' => 'posts', 'action' => 'view', $thisPost['Post']['id'])) . '", "' .
			$thisPost['Post']['minZoom'] . '");' . "\n";
		$cnt++;
	}
}

?>
</script>


<div class="row">
	<div class="span12" >
    	<div id="postsmapa" class="generalBgWrapperDiv">
			<h1><?php echo __('Map'); ?></h1>
				<div id="map-canvas" style="width:100%; height:400px;"></div>
		</div>
	</div>
</div>

