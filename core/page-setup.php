<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

// Add data text
if ( ! isset( $_txt_url ) ) {
	include SF_TFP_PLUGIN_DIR . '/languages/lang-base.php'; // connect base text variable
}
?>

	<div id="sf-wrapp" class="wrap">

		<h1 class="sf-head__h1"><?php _e( 'Tigris for Salesforce', SF_TFP_NAME ); ?></h1>
		<hr>
		<?php 
		if (! is_plugin_active( 'geoip-detect/geoip-detect.php' ) ) {
			$plugin_name = 'GeoIP Detection';
			$install_link = '<a href="' . esc_url(network_admin_url('plugin-install.php?tab=plugin-information&plugin=geoip-detect&TB_iframe=true&width=600&height=550')) . '" class="thickbox" title="'.__('More info about', SF_TFP_NAME).
			' GeoIP Detection">GeoIP Detection</a>';
		?>
		<div class="error notice is-dismissible">
			<p style="float: right">
				<h3>GeoIP Detection <?php _e( 'not installed', SF_TFP_NAME ); ?></h3>
			</p>
			<p>
				<?php _e( 'For works IP detection you need install plugin', SF_TFP_NAME ); ?> <?php echo $install_link?>
			</p>
		</div>
		<?php } ?>
		<?php
		if ( get_current_screen()->parent_base !== 'options-general' ) {
			settings_errors( str_replace( '-', '_', SF_TFP_NAME ) );
		}
		?>
		<div>
			<p><?php _e( 'To authenticate using OAuth, you must', SF_TFP_NAME ); ?> 
				<a href="https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/intro_defining_remote_access_applications.htm" target="_blank"><?php _e( 'create a connected app', SF_TFP_NAME ); ?></a>. 
				<?php _e( 'Use Call-back-URL:', SF_TFP_NAME ); ?> <b><?php echo get_site_url(); ?>/tigrisoauth-callback.php</b>
			 </p>
		<?php
		// Setting form
		?>
		<div class="sf-content">
			<form id="sf_key_number" name="sf_key_number" action="options.php" method="POST">
				<?php
				settings_fields( 'sf-tfp-settings' );
				do_settings_sections( 'sf-tigris-flexplatform' );
				$text =  __( 'Save settings', SF_TFP_NAME );
				$other_attributes = array();
				submit_button( $text, 'button-primary', 'submit', true, $other_attributes );
				?>
			</form>
		</div>

		<hr />

		<div class="sf-help">
			<p>Version: 1.0</p>
		</div>

	</div><!-- \#sf-wrapp -->