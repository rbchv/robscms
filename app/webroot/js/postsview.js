

var closeFunction = function(e)
{
	if((e == null) || (e.keyCode == 27))
	{
		window.location = '/';
	}
}

function makeBigPopover()
{
	var newXY = ($(document).width() > 600 ? 400 : $(document).width() * 0.7);
	$('#popoverStyle').html('div.popover { width:' + newXY +'px; max-width:' + newXY + 'px; height:' + newXY + 'px; max-height:' + newXY + 'px; }');
}

function makeSmallPopover()
{
	$('#popoverStyle').html('div.popover { }');
}

function hideAllPopoversExceptMe(thisIsMe)
{
	if(thisIsMe == null) thisIsMe = $();
	if(!thisIsMe.is($('img#showmapbtn'))) $('img#showmapbtn').popover('hide');
	if(!thisIsMe.is($('img#showexifbtn'))) $('img#showexifbtn').popover('hide');
	if(!thisIsMe.is($('img#keybtn'))) $('img#keybtn').popover('hide');
}

$(document).keyup(function(e)
{
	closeFunction(e);
});


$(document).ready(function(e)
{

	$(document).click(function(e)
	{
		hideAllPopoversExceptMe(null);
    });


	$('img#keybtn').popover({
		placement: 'top',
        html: true,
        trigger: 'manual'
    }).click(function()
	{
		hideAllPopoversExceptMe($('img#keybtn'));
		$('#popoverStyle').html('div.popover { }');
        $(this).popover('toggle');

        $('img#refreshbtn').click(function(e)
		{
			e.preventDefault();
			$('#keymessagearea').html(creatingNewKeyText + '...');

			$.ajax(
			{
				type: 'POST',
				url: creatingNewKeyUrl,
			}).fail(function(msg)
			{
				$('#keymessagearea').html('<span class="text-error">' + errorNewKeyText + '</span>');
			}).done(function(msg)
			{
				if(msg == '-1')
				{
					$('#keymessagearea').html('<span class="text-error">' + errorNewKeyText + '</span>');
				} else
				{
					$('#keymessagearea').html('<span class="text-success">' + successNewKeyText + '</span>');
					$('#accessKeyPre').html(msg);
				}

				$('#keymessagearea').animate(
				{
					opacity: 0
				}, 3000, function()
				{
					$('#keymessagearea').html('');
					$(this).css('opacity', 1);// Animation complete.
				});
			});
			return false;
		});

		return false;
    });

	$('img#showexifbtn').popover({
		placement: 'top',
        html: true,
        trigger: 'manual'
    }).click(function()
	{
		hideAllPopoversExceptMe($('img#showexifbtn'));
		$('#popoverStyle').html('div.popover { }');
        $(this).popover('toggle');
		return false;
    });

	$('img#showmapbtn').popover({
		placement: 'top',
        html: true,
        trigger: 'manual'
    }).click(function()
	{
		//All this is to avoid flickering when switching among popovers,
		//since they change sizes via styles
		if($('div.popover').length)
		{
			//Is showing. See if it's this guy's
			var isThisMine = $(this).next('div.popover').length;
			if(isThisMine)
			{
				hideAllPopoversExceptMe();
			} else
			{
				//Wait for it to be removed from the DOM to avoid flickering
				hideAllPopoversExceptMe($('img#showmapbtn'));

				var intervalCnt = 0;
				$('#popoverStyle').html('div.popover { display:none; }');
				var mySetInterval = setInterval(function()
				{
					intervalCnt++;
					if(($('div.popover').length == 0) || (intervalCnt > 40))
					{
						clearInterval(mySetInterval);
						if($(this).attr('data-content') != 'No data')
						{
							makeBigPopover();
						}
						$('img#showmapbtn').popover('show');
					}
				}, 50);
			}
		} else
		{
			if($(this).attr('data-content') != 'No data')
			{
				makeBigPopover();
			}
			$('img#showmapbtn').popover('show');
		}
		return false;
    });
});

