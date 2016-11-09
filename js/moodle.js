$(document).ready(function() {
	$('.toggler').click(function() {
		$('.toggler').removeClass('active');
		$(this).addClass('active').parent().children('ul').toggle();
		$(this).toggleClass('opened');
		$('input[name=categoryid]').val($(this).data('catid'));
	});
})