
$(document).ready(function()
{
	if(document.getElementById("map-canvas") !== null)
	{
    	map = new google.maps.Map(document.getElementById("map-canvas"));

		if(picMarkers.length)
		{
			for(i = 0; i < picMarkers.length; i++)
			{
				var position = new google.maps.LatLng(picMarkers[i][0], picMarkers[i][1]);
				marker = new google.maps.Marker(
				{
					position: position,
					map: map,
					picUrl: picMarkers[i][2],
					minZoom: picMarkers[i][3]
				});

				google.maps.event.addListener(marker, 'click', (function(marker, i)
				{
					return function()
					{
						var currentZoom = map.getZoom();
						if(currentZoom < marker.minZoom)
						{
							map.setZoom(parseInt(marker.minZoom));
							map.setCenter(marker.getPosition());
						} else
						{
							window.location.href = marker.picUrl;
						}
					}
				})(marker, i));
			}
		}
		map.setZoom(2);
		map.setCenter(new google.maps.LatLng(9.676568, -84.06618));
	}
});
