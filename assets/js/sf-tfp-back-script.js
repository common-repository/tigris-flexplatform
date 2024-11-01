/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
"use strict"
jQuery( document ).ready( function() {

	/** Event section */

	/** Hidden update status box */
	jQuery( '#update_status span' ).delay( 10000 ).slideUp();

	/** Close warning window */
	jQuery( '.sf-warning__close' ).click( function( event ) {
		jQuery( this ).parent().slideUp();
	})

	/** Copy to clipboard */
	jQuery( '.sf-input-copy' ).click( function( event ) {
		tfp_tigris_copy_to_clipboard( this );
	} );

	/** Function section */

	/**
	 * [tfp_tigris_copy_to_clipboard 	Copy content to clipboard]
	 * @param  {object} element 		[Current element]
	 * @return {string}         		[Puts data on the clipboard]
	 */
	function tfp_tigris_copy_to_clipboard( element ) {
		var copy_data  = jQuery( element ).prev().text(),
			copy_range = document.createElement( 'INPUT' ),
			copy_focus = document.activeElement;

		copy_range.value = copy_data;
		document.body.appendChild( copy_range );
		copy_range.select();
		document.execCommand( 'copy' );
		document.body.removeChild( copy_range );
		copy_focus.focus();

		jQuery( '#update_status' ).append( '<span class="correct"><i class="sf-warning__dashicons"></i>' + jQuery( element ).prev().data( 'info' )  + '</span>' );
		jQuery( '#update_status' ).delay( 100 ).slideDown();
		jQuery( '#update_status' ).delay( 10000 ).slideUp( function() {
			jQuery( '#update_status span' ).remove();
		} );
	}
} )