jQuery(document).ready(function ($) {
	$('span.activate').on('click', function () {
		const text = $(this).find('a').attr('aria-label');
		return window.confirm(text + '?');
	});
	$('span.deactivate').on('click', function () {
		const text = $(this).find('a').attr('aria-label');
		return window.confirm(text + '?');
	});
});
