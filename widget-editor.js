jQuery(document).ready( function($) {

	$('.widget-edit').on( 'click', function( ev ) {
		ev.preventDefault();

		$.post( widgetEditor.ajaxUrl, {
			action: 'fwe_widget_edit',
			widget_id: $(this).attr('data-widget-id')
		}, function( response ) {
			$('body').append( response.data );
		}, 'json' );
	});

	$('body').on('click', '.widget-edit-close', function( ev ) {
		ev.preventDefault();
		$(this).closest( '.widget-edit-wrapper').remove();
	});

	$('body').on('submit', '.widget-edit-form', function( ev ) {
		ev.preventDefault();
		var form = $(this),
			widgetId = form.find('.widget-id').val(),
			sidebarId = form.find('.sidebar').val(),
			data = form.closest('.widget-edit-form').serialize();

		a = {
			action: 'save-widget',
			savewidgets: $('#savewidgets').val(),
			sidebar: sidebarId
		};

		data += '&' + $.param(a);

		$.post( widgetEditor.ajaxUrl, data, function( response ) {
			if ( response ) {
				form.closest('.widget-edit-wrapper').remove();
				$.post( widgetEditor.ajaxUrl, {
					action: 'fwe_refresh_widget',
					widget_id: widgetId,
					sidebar_id: sidebarId
				}, function( response ) {
					// console.log( response );
					$('#'+widgetId).replaceWith( response.data );
				}, 'json' );

			}
		} );
	});

});