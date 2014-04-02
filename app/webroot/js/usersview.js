
function submitPermChange(me)
{
	$(me).css('display', 'none');
	$(me).parents('td').children('span.permsaving').css('display', 'block');
	$(me.form).submit();
}

$(document).ready(function()
{
	$('span.permtext').click(function(e)
	{
		$(this).css('display', 'none');
		$(this).next('span.permchange').css('display', 'block');
	});
});
