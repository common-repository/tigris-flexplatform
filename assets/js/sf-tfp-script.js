/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
"use strict"
jQuery( document ).ready( function() {

	/** AJAX vacations load */
	jQuery( function( e ) {
		jQuery( '#vacancy-load' ).click( function() {
			var button = jQuery( this ),
				button_txt = button.text(),
				button_ld  = button.children( 'span' ).data( 'load' ),
				data = {
					'action' : 'loadvacancies',
					'query'  : true_posts,
					'page'   : current_page
				};
			button.children( 'span' ).fadeOut( function() {
				jQuery( this ).text( button_ld ).fadeIn();
			} );

			jQuery.ajax( {
				url  : ajaxurl,
				data : data,
				type : 'POST',
				success: function( data ) {
					if ( data ) {
						button.children( 'span' ).fadeOut( function() {
							jQuery( this ).text( button_txt ).fadeIn();
						} );
						button.before( data );
						button.siblings( '.hidden' ).removeClass( 'hidden' );
						current_page++;
						if ( current_page == max_pages ) {
							jQuery( '#vacancy-load' ).fadeOut( function() {
								setTimeout( function() {
									jQuery( this ).remove();
								}, 5000 );
							} );
						}
					} else {
						jQuery( '#vacancy-load' ).slideUp( function() {
							setTimeout( function() {
								jQuery( this ).remove();
							}, 5000 );
						} );
					}
				}
			} );
		} );
	} );

	/** AJAX sending a form */
	jQuery( function( e ) {

		jQuery( '#send-form' ).click( function( event ) {
			event.stopPropagation();
			event.preventDefault();

			var required = 1;

			// checked require field
			jQuery( 'form.vacancy-form input[required]' ).each( function() {

				if ( jQuery( this ).val() == '' && jQuery( this ).attr( 'type' ) != 'email' ) {
					jQuery( this ).parent().addClass( 'required' );
					required = 0;
					validation_tooltip( this );
				}

				if ( jQuery( this ).attr( 'type' ) == 'checkbox' && ! jQuery( this ).prop( 'checked' ) ) {
					jQuery( this ).parent().addClass( 'required' );
					required = 0;
					validation_tooltip( this );
				}

				if ( jQuery( this ).attr( 'type' ) == 'email' && ! validation_email( jQuery( this ).val() ) ) {
					jQuery( this ).parent().addClass( 'required' );
					required = 0;
					validation_tooltip( this );
				}

				if ( jQuery( this ).attr( 'type' ) == 'file' && ! jQuery( '#file-cv' )[0].files.length ) {
					jQuery( this ).parent().addClass( 'required' );
					required = 0;
				}
			} );

			if ( ! required ) {
				jQuery( '.vacancy-form__response-output' ).text( jQuery( this ).data( 'required' ) );
				return false;
			}

			var form_data  = new FormData( jQuery( 'form.vacancy-form' )[0] ),
				button_txt = jQuery( this ).text(),
				button_ld  = jQuery( this ).data( 'load' );

			jQuery.ajax( {
				url		: ajaxurl,
				data		: form_data,
				cache           : false,
				headers         : { "cache-control": "no-cache" },
				contentType	: false,
				processData	: false,
				type		: 'POST',
				beforeSend	: function() {
					jQuery( '.vacancy-form__ajax-loader' ).fadeIn();
				},
				success		: function( respond, status, jqXHR ) {
					if ( typeof respond.error === 'undefined' && redirect == '0' ) {
						jQuery( 'form.vacancy-form' )[0].reset();
						jQuery( '.vacancy-form__ajax-loader' ).fadeOut();
						jQuery( '.vacancy-form__response-output' ).text( respond ).fadeIn( function() {
							var that = jQuery( this );
							setTimeout( function() {
								that.fadeOut();
							}, 5000 );
						} );
					} else if ( typeof respond.error === 'undefined' && redirect != '0' ) {
						window.location = redirect;
					} else {
						console.log( 'Error: ' + respond.error );
					}
				}
			} );
		} );
	} );

	/** Pre-validation e-mail */
	jQuery( '.vacancy-form__email' ).on( 'blur', 'input[name=email]', function() {
		if ( jQuery( this ).val() !== '' && ! validation_email( jQuery( this ).val() ) ) {
			jQuery( this ).val( '' ).parent().addClass( 'required' );
		}
	} );

	/** Clear fields */
	jQuery( 'form.vacancy-form' ).on( 'focus', 'input, select, textarea', function() {
		jQuery( this ).parents( '.required' ).removeClass( 'required' ).siblings( 'b' ).remove();
	} );

	/**
	 * [validation_email 				Pre-validation enter e-mail]
	 * @param  {string} email 			[Input value]
	 * @return {boloean}       			[Validation status]
	 */
	function validation_email( email ) {
		if ( ! email.match( /^[0-9a-z-_\.]+\@[0-9a-z-]+\.[a-z]{2,}$/i ) ) {
			return false;
		}
		return true;
	}

	/**
	 * [validation_tooltip description 	ToolTip for unvalidation field]
	 * @param  {object} el 				[Current element]
	 * @return {NaN}    				[No return]
	 */
	function validation_tooltip( el ) {
		var err = jQuery( el ).parents( 'label' );
		jQuery( '<b></b>' ).appendTo( err ).fadeOut( function() {
			jQuery( this ).text( jQuery( el ).data( 'error' ) ).fadeIn();
		} );
	}

	/** Recalculation number of vacancies */
	jQuery( '#tigris-searchform .js-search-form-item.tigris-search-form-location' ).on( 'blur', function( event ) {
		if (jQuery(this).val().length > 3) {
			getVacancyCorrectCount(jQuery(this));
		} else {
			jQuery('.js-tigris-search-form-submit strong').text(jQuery('.js-tigris-search-form-submit').data('total'));
		}

	} );
	jQuery( '#tigris-searchform .js-search-form-item.tigris-search-form-distances' ).change( function( event ) {
		getVacancyCorrectCount( jQuery( this ) );
	} );

	/** Autocomplete search popup */
	jQuery( '#s' ).on( 'keyup', function( event ) {
		getAutoCompletteData();
	} );

	/** Autocomplete search select */
	jQuery( '.tigris-search-form-label-find-job .js-autocomplete-result' ).on( 'click', 'span', function( event ) {
		event.preventDefault();
		var val = jQuery( this ).text();
		if ( jQuery( '#s' ).val != val ) {
			jQuery( '#s' ).val( val );
		}
		getAutoCompletteData();
		setTimeout( function() {
			jQuery( '.tigris-search-form-label-find-job .js-autocomplete-result' ).removeClass( 'active' );
		}, 1000 );
	} );
});

/**
 * [getVacancyCorrectCount 			Get vacancy number to search for search]
 * @param  {object} element 		[Active field]
 * @return {NaN}                    [No return]
 */
function getVacancyCorrectCount( element ) {
	var data = {
			'action' : 'correctcount',
			'query'  : jQuery('#tigris-searchform').serializeArray()
		},
		radius = jQuery('#tigris-searchform select').val();

   jQuery.ajax( {
        url: ajaxurl,
		data: data,
        type: 'POST',
        beforeSend: function() {
        	jQuery( '#tigris-searchform' ).append( '<div id="cap"></div>' );
        },
        success: function (data) {
			data = JSON.parse(data);
			for (var key in data) {
				if (key == 'count') {
					// vacancies exist
					if ((radius != '0' && data['post__in']) || (radius == '0' && data[key])) {
						jQuery('#tigris-searchform .js-tigris-search-form-submit strong').text(data[key]);
					}
					// vacancies don't exist
					if ((radius != '0' && !data['post__in']) || (radius == '0' && !data[key])) {
						jQuery('#tigris-searchform .js-tigris-search-form-submit strong').text( data['post__in']);
					}
				} else if (key == 'post__in') {
					jQuery('input[name=post__in]').val(data[key]);
				}
			}
			jQuery('#tigris-searchform > #cap').remove();
        }
    } );
}

/**
	 * [getAutoCompletteData 			Get autocomplete result for search]
	 * @return {NaN}                    [No return]
	 */
	function getAutoCompletteData() {
		jQuery('.tigris-search-form-label-find-job .js-autocomplete-result' ).removeClass( 'active' );
		if ( jQuery( '#s' ).val().length > 3 ) {
			var data = {
				'action' : 'autocomlete',
				'query'  : jQuery( '#s' ).val()
			};
	       jQuery.ajax( {
	            url: ajaxurl,
				data: data,
	            type: 'POST',
	            success: function ( data ) {
					data = JSON.parse( data );
					var html = '';
					for ( var key in data ) {
						if ( key != 'count' ) {
							html += '<div class="transition"><span>' + data[key] + '</span></div>';
						} else {
							jQuery( '#tigris-searchform .js-tigris-search-form-submit strong' ).text( data[key] );
							jQuery( '#tigris-searchform' ).data( 'total', data[key] );
						}
					}
					if ( html.length > 0 ) {
						jQuery('.tigris-search-form-label-find-job .js-autocomplete-result').html( html );
						if ( ! jQuery( '.tigris-search-form-label-find-job .js-autocomplete-result' ).hasClass( 'active' ) ) {
							jQuery( '.tigris-search-form-label-find-job .js-autocomplete-result' ).addClass( 'active' );
						}
					} else {
						jQuery( '.tigris-search-form-label-find-job .js-autocomplete-result' ).removeClass( 'active' );
					}
	            }
	        } );
		} else {
			jQuery( '#tigris-searchform .js-tigris-search-form-submit strong' ).text( jQuery( '#tigris-searchform' ).data( 'total' ) );
		}
	}
